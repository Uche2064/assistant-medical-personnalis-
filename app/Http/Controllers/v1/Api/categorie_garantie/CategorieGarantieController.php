<?php

namespace App\Http\Controllers\v1\Api\categorie_garantie;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\categorie_garantie\StoreCategorieGarantieFormRequest;
use App\Http\Requests\categorie_garantie\UpdateCategorieGarantieFormRequest;
use App\Http\Resources\CategorieGarantieResource;
use App\Models\CategorieGarantie;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
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

        $query = CategorieGarantie::with('garanties', 'medecinControleur')
            ->withCount('garanties');

        if ($search) {
            $query->where('libelle', 'like', '%' . $search . '%');
        }

        $categories = $query->orderBy('created_at', 'desc')->paginate($perPage);

        $paginatedData = new LengthAwarePaginator(
            CategorieGarantieResource::collection($categories),
            $categories->total(),
            $categories->perPage(),
            $categories->currentPage(),
            ['path' => Paginator::resolveCurrentPath()]
        );
        return ApiResponse::success($paginatedData, 'Liste des catégories de garanties récupérée avec succès', 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeCategorieGarantie(StoreCategorieGarantieFormRequest $request)
    {
        try {
            DB::beginTransaction();

            $medecinControleur = Auth::user();
            $data = $request->validated();
            $libelle = strtolower(trim($data['libelle']));

            // Vérifier si une catégorie supprimée existe avec le même libellé
            $categorieExistante = CategorieGarantie::withTrashed()
                ->where('libelle', $libelle)
                ->first();

            if ($categorieExistante) {
                if ($categorieExistante->trashed()) {
                    // Restaurer la catégorie existante
                    $categorieExistante->restore();
                    $categorieExistante->update([
                        'description' => strtolower(trim($data['description'] ?? null)),
                        'medecin_controleur_id' => $medecinControleur->id,
                    ]);
                    $categorieGarantie = $categorieExistante;
                } else {
                    // Catégorie existante non supprimée
                    return ApiResponse::error('Cette catégorie existe déjà', 422);
                }
            } else {
                // Créer une nouvelle catégorie
                $categorieGarantie = CategorieGarantie::create([
                    'libelle' => $libelle,
                    'description' => strtolower(trim($data['description'] ?? null)),
                    'medecin_controleur_id' => $medecinControleur->id,
                ]);
            }

            DB::commit();

            return ApiResponse::success([
                "categorie_garantie" => $categorieGarantie->load('garanties', 'medecinControleur')
            ], 'Catégorie de garantie créée avec succès', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function showCategorieGarantie(string $id)
    {
        $categorieGarantie = CategorieGarantie::with('garanties')->find($id);

        if (!$categorieGarantie) {
            return ApiResponse::error('Catégorie de garantie non trouvée', 404);
        }

        return ApiResponse::success($categorieGarantie, 'Catégorie de garantie récupérée avec succès');
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateCategorieGarantie(UpdateCategorieGarantieFormRequest $request, int $id)
    {
        $categorieGarantie = CategorieGarantie::with('garanties')->find($id);

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
                $updates['description'] = trim($data['description']);
            }

            if (!empty($updates)) {
                $categorieGarantie->update($updates);
            }

            DB::commit();

            return ApiResponse::success([
                "categorie_garantie" => $categorieGarantie->load('garanties', 'medecinControleur')
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
        $categorieGarantie = CategorieGarantie::with('garanties')->find($id);

        if (!$categorieGarantie) {
            return ApiResponse::error('Catégorie de garantie non trouvée', 404);
        }

        $categorieGarantie->delete();
        return ApiResponse::success(null, 'Catégorie de garantie supprimée avec succès', 204);
    }
}
