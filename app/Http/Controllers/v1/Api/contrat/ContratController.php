<?php

namespace App\Http\Controllers\v1\Api\contrat;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\contrat\StoreContratFormRequest;
use App\Http\Requests\contrat\UpdateContratFormRequest;
use App\Http\Resources\ContratResource;
use App\Http\Resources\ContratCollection;
use App\Http\Resources\CategorieGarantieResource;
use App\Models\Contrat;
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
    
        $query = Contrat::with(['technicien', 'categoriesGaranties.garanties'])
            ->when($search, function ($q, $search) {
                $q->where('type_contrat', 'like', '%' . $search . '%');
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
        $contrat = Contrat::with(['technicien', 'categoriesGaranties.garanties'])
            ->find($id);

        if (!$contrat) {
            return ApiResponse::error('Contrat non trouvé', 404);
        }

        return ApiResponse::success(
            new ContratResource($contrat),
            'Contrat récupéré avec succès'
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
            $contrat = Contrat::create([
                'type_contrat' => $validatedData['type_contrat'],
                'prime_standard' => $validatedData['prime_standard'],
                'technicien_id' => $technicien->id,
                'est_actif' => true,
                'categories_garanties_standard' => collect($validatedData['categories_garanties'])
                    ->pluck('categorie_garantie_id')
                    ->toArray(),
                'couverture' => $validatedData['couverture'],
                'couverture_moyenne' => collect($validatedData['categories_garanties'])
                    ->pluck('couverture')
                    ->filter()
                    ->avg(),
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
                'Contrat créé avec succès',
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
            $contrat = Contrat::find($id);
            if (!$contrat) {
                return ApiResponse::error('Contrat non trouvé', 404);
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
                'Contrat mis à jour avec succès'
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
            $contrat = Contrat::find($id);
            if (!$contrat) {
                return ApiResponse::error('Contrat non trouvé', 404);
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

            return ApiResponse::success(null, 'Contrat supprimé avec succès');
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
                'total' => Contrat::count(),
                'actifs' => Contrat::where('est_actif', true)->count(),
                'suspendus' => Contrat::where('est_actif', false)->count(),
                'type_contrat' => Contrat::select('type_contrat', DB::raw('COUNT(*) as count'))
                    ->groupBy('type_contrat')
                    ->get()
                    ->mapWithKeys(function ($item) {
                        $typeValue = is_object($item->type_contrat) ? $item->type_contrat->value : $item->type_contrat;
                        return [$typeValue ?? 'Non spécifié' => $item->count];
                    }),
                'repartition_prix' => [
                    '0-25000' => Contrat::where('prime_standard', '<=', 25000)->count(),
                    '25001-50000' => Contrat::whereBetween('prime_standard', [25001, 50000])->count(),
                    '50001-75000' => Contrat::whereBetween('prime_standard', [50001, 75000])->count(),
                    '75001+' => Contrat::where('prime_standard', '>', 75000)->count(),
                ],
            ];

            return ApiResponse::success($stats, 'Statistiques des contrats');
        } catch (\Exception $e) {
            return ApiResponse::error('Erreur lors de la récupération des statistiques: ' . $e->getMessage(), 500);
        }
    }
}
