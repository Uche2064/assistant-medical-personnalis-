<?php

namespace App\Http\Controllers\v1\Api\garanties;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\garanties\StoreGarantieFormRequest;
use App\Http\Requests\garanties\UpdateGarantieFormRequest;
use App\Http\Resources\GarantieResource;
use App\Models\CategorieGarantie;
use App\Models\Garantie;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GarantieController extends Controller
{
    /**
     * Liste toutes les garanties avec filtre.
     */
    public function indexGaranties(Request $request)
    {
        $query = Garantie::with(['categorieGarantie', 'medecinControleur']);


        $query->orderBy('created_at', 'desc');

        $garanties = $query->get();

        return ApiResponse::success(GarantieResource::collection($garanties), 'Liste des garanties récupérée avec succès', 200);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function storeGarantie(StoreGarantieFormRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $libelle = strtolower(trim($data['libelle']));
            $categorieId = $data['categorie_garantie_id'];

            // vérifier que le libellé est unique pour la catégorie choisie
            $existingGarantie = Garantie::whereRaw('LOWER(libelle) = ?', [$libelle])
                ->where('categorie_garantie_id', $categorieId)
                ->exists();

            if ($existingGarantie) {
                return ApiResponse::error("Une garantie avec le même libellé existe déjà dans cette catégorie");
            }

            // Créer une nouvelle garantie
            $garantie = Garantie::create([
                'libelle' => $libelle,
                'plafond' => $data['plafond'],
                'taux_couverture' => $data['taux_couverture'],
                'prix_standard' => $data['prix_standard'],
                'description' => trim($data['description'] ?? null),
                'est_active' => $data['est_active'],
                'categorie_garantie_id' => $categorieId,
                'medecin_controleur_id' => Auth::user()->personnel->id,
            ]);
            $garantie->load(['categorieGarantie', 'medecinControleur']);
            DB::commit();

            return ApiResponse::success(new GarantieResource($garantie), 'Garantie créée avec succès', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création de la garantie', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return ApiResponse::error('Une erreur est survenue lors de la création de la garantie', 500, $e->getMessage());
        }
    }
    /**
     * Display the specified resource.
     */
    public function showGarantie(string $id)
    {
        $garantie = Garantie::with(['categorieGarantie', 'medecinControleur'])->find($id);

        if (!$garantie) {
            return ApiResponse::error('Garantie non trouvée', 404);
        }

        return ApiResponse::success(new GarantieResource($garantie), 'Garantie récupérée avec succès');
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateGarantie(UpdateGarantieFormRequest $request, int $id)
    {
        try {

            $data = $request->validated();
            $garantie = Garantie::with(['categorieGarantie', 'medecinControleur'])->find($id);

            if (!$garantie) {
                return ApiResponse::error('Garantie non trouvée', 404);
            }

            $libelle = strtolower(trim($data['libelle'] ?? $garantie->libelle));
            $catGarantieId = $data['categorie_garantie_id'] ?? $garantie->categorie_garantie_id;

            DB::beginTransaction();
            // vérifier que la categorie de garantie existe
            $catGarantie = CategorieGarantie::find($catGarantieId);

            // Mise à jour normale
            $garantie->update([
                'libelle' => $libelle,
                'plafond' => $data['plafond'] ?? $garantie->plafond,
                'taux_couverture' => $data['taux_couverture'] ?? $garantie->taux_couverture,
                'prix_standard' => $data['prix_standard'] ?? $garantie->prix_standard,
                'description' => trim($data['description'] ?? $garantie->description),
                'est_active' => $data['est_active'] ?? $garantie->est_active,
                'categorie_garantie_id' => $catGarantieId,
                'medecin_controleur_id' => Auth::user()?->personnel->id ?? null,
            ]);

            DB::commit();

            return ApiResponse::success(new GarantieResource($garantie),  'Garantie mise à jour avec succès');
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error($e->getMessage(), 500);
        }
    }


    /**
     * Supprimer une garantie.
     */
    public function destroyGarantie($id)
    {
        $garantie = Garantie::find($id);

        if (!$garantie) {
            return ApiResponse::error('Garantie non trouvée', 404);
        }

        $garantie->delete();
        return ApiResponse::success(null, 'Garantie supprimée avec succès', 204);
    }

    public function toggleGarantieStatus($id)
    {
        $garantie = Garantie::find($id);

        if (!$garantie) {
            return ApiResponse::error('Garantie non trouvée', 404);
        }

        $garantie->update([
            'est_active' => !$garantie->est_active
        ]);
        $status = $garantie->est_active ? 'activée' : 'désactivé';
        return ApiResponse::success(new GarantieResource($garantie), 'Garantie '. $status . ' avec succès');

    }
}
