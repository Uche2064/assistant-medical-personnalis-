<?php

namespace App\Http\Controllers\v1\Api\categorie_garantie;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\categorie_garantie\StoreCategorieGarantieFormRequest;
use App\Http\Requests\categorie_garantie\UpdateCategorieGarantieFormRequest;
use App\Models\CategoriesGaranties;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CategorieGarantieController extends Controller
{
    /**
     * Lister toutes les catégories de garantie avec filtre.
     */
public function indexCategorieGarantie(Request $request)
{
    $search = $request->query('search');
    $perPage = $request->query('per_page', 10);

    $query = CategoriesGaranties::with('garanties')
                ->withCount('garanties');

    if ($search) {
        $query->where('libelle', 'like', '%' . $search . '%');
    }

    $categories = $query->orderBy('created_at', 'desc')->paginate($perPage);

    return ApiResponse::success($categories, 'Liste des catégories de garanties récupérée avec succès');
}

    /**
     * Store a newly created resource in storage.
     */
    public function storeCategorieGarantie(StoreCategorieGarantieFormRequest $request)
    {
        $data = $request->validated();
        $medecinControleur = Auth::user()->personnel;

        try {
            DB::beginTransaction();

            $categorieGarantie = CategoriesGaranties::create([
                'libelle' => strtolower(trim($data['libelle'])),
                'description' => strtolower(trim($data['description'])) ?? null,
                'medecin_controleur_id' => $medecinControleur->id,
            ]);

            DB::commit();

            return ApiResponse::success([
                'id' => $categorieGarantie->id,
                'libelle' => $categorieGarantie->libelle,
                'description' => $categorieGarantie->description,
            ], 'Catégorie de garantie créée avec succès', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Erreur lors de la création de la catégorie de garantie', 400, $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function showCategorieGarantie(string $id)
    {
        $categorieGarantie = CategoriesGaranties::with('garanties')->find($id);

        if (!$categorieGarantie) {
            return ApiResponse::error('Catégorie de garantie non trouvée', 404);
        }

        return ApiResponse::success($categorieGarantie, 'Catégorie de garantie récupérée avec succès');
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateCategorieGarantie(UpdateCategorieGarantieFormRequest $request, string $id)
    {
        $categorieGarantie = CategoriesGaranties::with('garanties')->find($id);

        if (!$categorieGarantie) {
            return ApiResponse::error('Catégorie de garantie non trouvée', 404);
        }

        $data = $request->validated();

        try {
            DB::beginTransaction();

            $updates = [];

            if (isset($data['libelle'])) {
                $updates['libelle'] = strtolower(trim($data['libelle']));
            }

            if (isset($data['description'])) {
                $updates['description'] = strtolower(trim($data['description']));
            }

            if (!empty($updates)) {
                $categorieGarantie->update($updates);
            }

            DB::commit();

            return ApiResponse::success([
                'id' => $categorieGarantie->id,
            ], 'Catégorie de garantie mise à jour avec succès');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erreur update catégorie garantie ID: $id", ['exception' => $e]);
            return ApiResponse::error('Erreur lors de la mise à jour de la catégorie de garantie', 500, $e->getMessage());
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroyCategorieGarantie(string $id)
    {
        $categorieGarantie = CategoriesGaranties::with('garanties')->find($id);

        if (!$categorieGarantie) {
            return ApiResponse::error('Catégorie de garantie non trouvée', 404);
        }

        $categorieGarantie->delete();
        return ApiResponse::success(null, 'Catégorie de garantie supprimée avec succès', 204);
    }
}
