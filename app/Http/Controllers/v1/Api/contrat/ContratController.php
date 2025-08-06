<?php

namespace App\Http\Controllers\v1\Api\contrat;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\contrat\StoreContratFormRequest;
use App\Http\Requests\contrat\UpdateContratFormRequest;
use App\Models\Contrat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\CategorieGarantie;

class ContratController extends Controller
{
    /**
     * Récupérer les catégories de garanties disponibles
     */
    public function getCategoriesGaranties()
    {
        $categories = CategorieGarantie::with('garanties')->get();

        return ApiResponse::success($categories, 'Catégories de garanties récupérées avec succès');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Récupération des filtres depuis la requête
        $type = $request->query('type'); // basic, standard, premium
        $min = $request->query('min');   // montant minimum
        $max = $request->query('max');   // montant maximum

        $query = Contrat::query();

        // Filtre par type de contrat
        if ($type) {
            $query->where('type_contrat', $type);
        }

        // Filtre par montant
        if ($min !== null) {
            $query->where('prime_standard', '>=', $min);
        }

        if ($max !== null) {
            $query->where('prime_standard', '<=', $max);
        }

        // Tu peux ajouter une pagination si tu veux
        $contrats = $query->with(['client', 'technicien'])->latest()->get();

        return ApiResponse::success($contrats, 'Liste des contrats récupérée avec succès');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreContratFormRequest $request)
    {
        dd($request->validated());
        $validatedData = $request->validated();

        try {
            DB::beginTransaction();

            // Création du contrat
            $contrat = Contrat::create([
                'type_contrat' => $validatedData['type_contrat'],
                'prime_standard' => $validatedData['prime_standard'],
                'technicien_id' => Auth::user()->id,
            ]);

            // Assignation des catégories de garanties
            if (isset($validatedData['categories_garanties'])) {
                foreach ($validatedData['categories_garanties'] as $categorieData) {
                    $contrat->categoriesGaranties()->attach(
                        $categorieData['categorie_garantie_id'],
                        ['couverture' => $categorieData['couverture']]
                    );
                }
            }

            // Chargement des relations nécessaires
            $contrat->load(['technicien', 'categoriesGaranties']);

            DB::commit();

            return ApiResponse::success($contrat, 'Contrat créé avec succès');
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
        // Validate the request
        $validatedData = $request->validated();

        // Update the contrat
        $contrat = Contrat::find($id);
        if (!$contrat) {
            return ApiResponse::error('Contrat non trouvé', 404);
        }
        $contrat->update($validatedData);

        return ApiResponse::success($contrat, 'Contrat mis à jour avec succès');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Find the contrat
        $contrat = Contrat::find($id);        
        if (!$contrat) {
            return ApiResponse::error('Contrat non trouvé', 404);
        }

        // Delete the contrat
        $contrat->delete();        

        return ApiResponse::success('Contrat supprimé avec succès');
    }

    public function stats() {
        $stats = [
            'total' => Contrat::count(),
            'actifs' => Contrat::where('est_actif', true)->count(),
            'suspendus' => Contrat::where('est_actif', false)->count(),
            'type_contrat' => Contrat::select('type_contrat', DB::raw('COUNT(*) as count'))
                ->groupBy('type_contrat')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->type_contrat ?? 'Non spécifié' => $item->count];
                }),
        ];

        return ApiResponse::success($stats, 'Statistiques des contrats');
    }
}
