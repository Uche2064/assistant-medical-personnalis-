<?php

namespace App\Http\Controllers\v1\Api\garanties;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\garanties\StoreGarantieFormRequest;
use App\Http\Requests\garanties\UpdateGarantieFormRequest;
use App\Http\Resources\GarantieResource;
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
        $search = $request->query('search');
        $perPage = $request->query('per_page', 10);

        $query = Garantie::with('categorieGarantie');   

        if ($search) {
            $query->where('libelle', 'like', '%' . $search . '%');
        }
        if ($request->query('categorie_garantie_id')) {
            $query->where('categorie_garantie_id', $request->query('categorie_garantie_id'));
        }

        $query->orderBy('created_at', 'desc');

        $garanties = $query->paginate($perPage);

        $paginatedData = new LengthAwarePaginator(
            GarantieResource::collection($garanties),
            $garanties->total(),
            $garanties->perPage(),
            $garanties->currentPage(),
            ['path' => Paginator::resolveCurrentPath()]
        );

        return ApiResponse::success($paginatedData, 'Liste des garanties récupérée avec succès', 200);
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

            // Vérifier si une garantie (non supprimée) existe avec le même libellé dans la même catégorie
            $garantieExistante = Garantie::where('libelle', $libelle)
                ->where('categorie_garantie_id', $categorieId)
                ->first();

            if ($garantieExistante) {
                return ApiResponse::error('Cette garantie existe déjà dans cette catégorie', 422);
            }

            // Vérifier si une garantie supprimée existe
            $garantieSupprimee = Garantie::withTrashed()
                ->where('libelle', $libelle)
                ->where('categorie_garantie_id', $categorieId)
                ->first();

            if ($garantieSupprimee) {
                // Restaurer et mettre à jour la garantie existante
                $garantieSupprimee->restore();
                $garantieSupprimee->update([
                    'plafond' => $data['plafond'],
                    'taux_couverture' => $data['taux_couverture'],
                    'prix_standard' => $data['prix_standard'],
                    'description' => trim($data['description'] ?? null),
                    'deleted_at' => null
                ]);
                $garantie = $garantieSupprimee;
            } else {
                // Créer une nouvelle garantie
                $garantie = Garantie::create([
                    'libelle' => $libelle,
                    'plafond' => $data['plafond'],
                    'taux_couverture' => $data['taux_couverture'],
                    'prix_standard' => $data['prix_standard'],
                    'description' => trim($data['description'] ?? null),
                    'categorie_garantie_id' => $categorieId,
                    'medecin_controleur_id' => Auth::user()->personnel->id,
                ]);
            }

            DB::commit();

            return ApiResponse::success(null, 'Garantie créée avec succès', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création de la garantie', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return ApiResponse::error('Une erreur est survenue lors de la création de la garantie', 500);
        }
    }
    /**
     * Display the specified resource.
     */
    public function showGarantie(string $id)
    {
        $garantie = Garantie::with('categorieGarantie')->find($id);

        if (!$garantie) {
            return ApiResponse::error('Garantie non trouvée', 404);
        }

        return ApiResponse::success($garantie, 'Garantie récupérée avec succès');
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateGarantie(UpdateGarantieFormRequest $request, int $id)
    {
        try {
            DB::beginTransaction();
    
            $data = $request->validated();
            $garantie = Garantie::find($id);
    
            if (!$garantie) {
                return ApiResponse::error('Garantie non trouvée', 404);
            }
    
            $libelle = strtolower(trim($data['libelle'] ?? $garantie->libelle));
            $categorieId = $data['categorie_garantie_id'] ?? $garantie->categorie_garantie_id;
    
            // Vérifier s’il existe une autre garantie identique (hors elle-même)
            $existing = Garantie::withTrashed()
                ->where('libelle', $libelle)
                ->where('categorie_garantie_id', $categorieId)
                ->where('id', '!=', $garantie->id)
                ->first();
    
            if ($existing) {
                if ($existing->trashed()) {
                    $existing->restore();
                    $existing->update([
                        'description' => trim($data['description'] ?? $existing->description),
                        'taux_couverture' => $data['taux_couverture'] ?? $existing->taux_couverture,
                        'plafond' => $data['plafond'] ?? $existing->plafond,
                        'prix_standard' => $data['prix_standard'] ?? $existing->prix_standard,
                        'categorie_garantie_id' => $categorieId,
                    ]);
                    // Supprimer ou désactiver l’ancienne garantie
                    $garantie->delete(); // soft delete si activé
                    $garantie = $existing;
                } else {
                    return ApiResponse::error('Cette garantie existe déjà dans cette catégorie', 422);
                }
            } else {
                // Mise à jour normale
                $garantie->update([
                    'libelle' => $libelle,
                    'plafond' => $data['plafond'] ?? $garantie->plafond,
                    'taux_couverture' => $data['taux_couverture'] ?? $garantie->taux_couverture,
                    'prix_standard' => $data['prix_standard'] ?? $garantie->prix_standard,
                    'description' => trim($data['description'] ?? $garantie->description),
                    'categorie_garantie_id' => $categorieId,
                    'medecin_controleur_id' => Auth::user()?->personnel->id ?? null,
                ]);
            }
    
            DB::commit();
    
            return ApiResponse::success([
                'garantie' => $garantie->load('categorieGarantie')
            ], 'Garantie mise à jour avec succès');
    
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
    

    /**
     * Supprimer une garantie.
     */
    public function destroyGarantie(string $id)
    {
        $garantie = Garantie::find($id);

        if (!$garantie) {
            return ApiResponse::error('Garantie non trouvée', 404);
        }

        $garantie->delete();
        return ApiResponse::success(null, 'Garantie supprimée avec succès', 204);
    }
}
