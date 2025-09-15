<?php

namespace App\Http\Controllers\v1\Api\contrat;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\contrat\StoreContratFormRequest;
use App\Http\Requests\contrat\UpdateContratFormRequest;
use App\Http\Resources\ContratResource;
use App\Http\Resources\ContratCollection;
use App\Http\Resources\CategorieGarantieResource;
use App\Models\TypeContrat;
use App\Models\CategorieGarantie;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ContratController extends Controller
{
    /**
     * Récupérer les catégories de garanties disponibles
     */
    public function getCategoriesGaranties()
    {
        $categories = CategorieGarantie::with(['garanties', 'medecinControleur.user'])->get();

        return ApiResponse::success(
            CategorieGarantieResource::collection($categories),
            'Catégories de garanties récupérées avec succès'
        );
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Log::info($request->all());
    
        $search = $request->query('search');   // recherche
        $perPage = $request->query('per_page', 15);
        $estActif = $request->query('est_actif');   // statut
    
        $query = TypeContrat::with(['technicien', 'categoriesGaranties.garanties', 'assures'])
            ->when($search, function ($q, $search) {
                $q->where('libelle', 'like', '%' . $search . '%');
            })
            ->when(!is_null($estActif), function ($q) use ($estActif) {
                $q->where('est_actif', filter_var($estActif, FILTER_VALIDATE_BOOLEAN));
            });
    
        $contrats = $query->latest()->paginate($perPage);
    
        $paginatedData = new LengthAwarePaginator(
            ContratResource::collection($contrats->items()),
            $contrats->total(),
            $contrats->perPage(),
            $contrats->currentPage()
        );
    
        return ApiResponse::success(
            $paginatedData,
            'Liste des contrats récupérée avec succès'
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $contrat = TypeContrat::with(['technicien', 'categoriesGaranties.garanties', 'assures'])
            ->find($id);

        if (!$contrat) {
            return ApiResponse::error('TypeContrat non trouvé', 404);
        }

        return ApiResponse::success(
            new ContratResource($contrat),
            'TypeContrat récupéré avec succès'
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreContratFormRequest $request)
    {
        $validatedData = $request->validated();

        try {
            DB::beginTransaction();

            // Récupérer l'utilisateur connecté (technicien)
            $technicien = Auth::user()->personnel;

            // Création du contrat
            $contrat = TypeContrat::create([
                'libelle' => $validatedData['libelle'],
                'prime_standard' => $validatedData['prime_standard'],
                'technicien_id' => $technicien->id,
                'est_actif' => true,
                'categories_garanties_standard' => collect($validatedData['categories_garanties'])
                    ->pluck('categorie_garantie_id')
                    ->toArray(),
                'couverture' => $validatedData['couverture'],
                'frais_gestion' => $validatedData['frais_gestion'],
                'prime_totale' => $validatedData['frais_gestion'] + $validatedData['prime_standard']
            ]);

            // Assignation des catégories de garanties
            if (isset($validatedData['categories_garanties'])) {
                foreach ($validatedData['categories_garanties'] as $categorieData) {
                    $contrat->categoriesGaranties()->attach(
                        $categorieData['categorie_garantie_id'],
                        ['couverture' => $validatedData['couverture']]
                    );
                }
            }

            // Chargement des relations nécessaires
            $contrat->load(['technicien', 'categoriesGaranties.garanties']);

            DB::commit();

            return ApiResponse::success(
                new ContratResource($contrat),
                'TypeContrat créé avec succès',
                201
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Erreur lors de la création du contrat: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateContratFormRequest $request, string $id)
    {
        $validatedData = $request->validated();

        try {
            DB::beginTransaction();

            // Trouver le contrat
            $contrat = TypeContrat::find($id);
            if (!$contrat) {
                return ApiResponse::error('TypeContrat non trouvé', 404);
            }

            // Vérifier que l'utilisateur est le technicien qui a créé le contrat
            $technicien = Auth::user()->personnel;
            if ($contrat->technicien_id !== $technicien->id) {
                return ApiResponse::error('Vous n\'êtes pas autorisé à modifier ce contrat', 403);
            }

            // Mise à jour du contrat
            $contrat->update($validatedData);

            // Chargement des relations
            $contrat->load(['technicien', 'categoriesGaranties.garanties']);

            DB::commit();

            return ApiResponse::success(
                new ContratResource($contrat),
                'TypeContrat mis à jour avec succès'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Erreur lors de la mise à jour du contrat: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            // Trouver le contrat
            $contrat = TypeContrat::find($id);
            if (!$contrat) {
                return ApiResponse::error('TypeContrat non trouvé', 404);
            }

            // Vérifier que l'utilisateur est le technicien qui a créé le contrat
            $technicien = Auth::user()->personnel;
            if ($contrat->technicien_id !== $technicien->id) {
                return ApiResponse::error('Vous n\'êtes pas autorisé à supprimer ce contrat', 403);
            }

            // Supprimer les relations avec les catégories
            $contrat->categoriesGaranties()->detach();

            // Supprimer le contrat (soft delete)
            $contrat->delete();

            DB::commit();

            return ApiResponse::success(null, 'TypeContrat supprimé avec succès');
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Erreur lors de la suppression du contrat: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Statistiques des contrats
     */
    public function stats()
    {
        try {
            $stats = [
                'total' => TypeContrat::count(),
                'actifs' => TypeContrat::where('est_actif', true)->count(),
                'suspendus' => TypeContrat::where('est_actif', false)->count(),
                'libelle' => TypeContrat::select('libelle', DB::raw('COUNT(*) as count'))
                    ->groupBy('libelle')
                    ->get()
                    ->mapWithKeys(function ($item) {
                        $typeValue = is_object($item->libelle) ? $item->libelle->value : $item->libelle;
                        return [$typeValue ?? 'Non spécifié' => $item->count];
                    }),
                'repartition_prix' => [
                    '0-25000' => TypeContrat::where('prime_standard', '<=', 25000)->count(),
                    '25001-50000' => TypeContrat::whereBetween('prime_standard', [25001, 50000])->count(),
                    '50001-75000' => TypeContrat::whereBetween('prime_standard', [50001, 75000])->count(),
                    '75001+' => TypeContrat::where('prime_standard', '>', 75000)->count(),
                ],
            ];

            return ApiResponse::success($stats, 'Statistiques des contrats');
        } catch (\Exception $e) {
            return ApiResponse::error('Erreur lors de la récupération des statistiques: ' . $e->getMessage(), 500);
        }
    }
}
