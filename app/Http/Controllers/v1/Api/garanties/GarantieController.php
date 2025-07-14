<?php

namespace App\Http\Controllers\v1\Api\garanties;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\garanties\StoreGarantieFormRequest;
use App\Http\Requests\garanties\UpdateGarantieFormRequest;
use App\Models\CategoriesGaranties;
use App\Models\Garantie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GarantieController extends Controller
{
    /**
     * Liste toutes les garanties avec filtre.
     */
    public function index(Request $request)
    {
        // Récupération des paramètres de filtre
        $search = $request->query('search'); // mot-clé pour libellé
        $perPage = $request->query('per_page', 10); // nombre d'éléments par page

        // Construction de la requête
        $query = Garantie::query(); // charge la relation si elle existe

        // Filtre par type (lié au type de contrat)


        // Recherche par libellé
        if ($search) {
            $query->where('libelle', 'like', '%' . $search . '%');
        }

        // Tri optionnel (par libellé croissant)
        $query->orderBy('created_at', 'desc');

        // Pagination
        $garanties = $query->paginate($perPage);

        return ApiResponse::success($garanties, 'Liste des garanties récupérée avec succès');
    }




    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGarantieFormRequest $request)
    {

        $data = $request->validated();
        $medecinControleur = Auth::user()->personnel;

        try {
            DB::beginTransaction();
            if (!CategoriesGaranties::find($data['categorie_garantie_id'])) {
                return ApiResponse::error('Catégorie de garantie non trouvée', 404);
            }
            $garantie = Garantie::create([
                'libelle' => trim($data['libelle']),
                'categorie_garantie_id' => $data['categorie_garantie_id'],
                'plafond' => $data['plafond'],
                'taux_couverture' => $data['taux_couverture'],
                'prix_standard' => $data['prix_standard'],
                'medecin_controleur_id' => $medecinControleur->id,
            ]);

            DB::commit();

            return ApiResponse::success([
                'id' => $garantie->id,
                'libelle' => $garantie->libelle,
                'categorie_garantie_id' => $garantie->categorie_garantie_id,
                'plafond' => $garantie->plafond,
                'medecin_controleur' => $medecinControleur->id,
            ], 'Garantie créée avec succès', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Erreur lors de la création de la garantie', 400, $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $garantie = Garantie::with('categorie')->find($id);

        if (!$garantie) {
            return ApiResponse::error('Garantie non trouvée', 404);
        }

        return ApiResponse::success($garantie, 'Garantie récupérée avec succès');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGarantieFormRequest $request, string $id)
    {
        $garantie = Garantie::with('categorie')->find($id);

        if (!$garantie) {
            return ApiResponse::error('Garantie non trouvée', 404);
        }

        $data = $request->validated();

        if (isset($data['categorie_garantie_id']) && !CategoriesGaranties::find($data['categorie_garantie_id'])) {
            return ApiResponse::error('Catégorie de garantie non trouvée', 404);
        }

        try {
            DB::beginTransaction();

            $updates = [];

            if (isset($data['libelle'])) {
                $updates['libelle'] = trim($data['libelle']);
            }

            if (isset($data['categorie_garantie_id'])) {
                $updates['categorie_garantie_id'] = $data['categorie_garantie_id'];
            }

            if (isset($data['plafond'])) {
                $updates['plafond'] = $data['plafond'];
            }

            if (isset($data['prix_standard'])) {
                $updates['prix_standard'] = $data['prix_standard'];
            }

            if (isset($data['taux_couverture'])) {
                $updates['taux_couverture'] = $data['taux_couverture'];
            }

            $garantie->update($updates);

            DB::commit();

            return ApiResponse::success([
                'id' => $garantie->id,
                'libelle' => $garantie->libelle,
                'categorie_garantie_id' => $garantie->categorie_garantie_id,
                'plafond' => $garantie->plafond,
            ], 'Garantie mise à jour avec succès');
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Erreur lors de la mise à jour de la garantie', 400, $e->getMessage());
        }
    }

    /**
     * Supprimer une garantie.
     */
    public function destroy(string $id)
    {
        $garantie = Garantie::find($id);

        if (!$garantie) {
            return ApiResponse::error('Garantie non trouvée', 404);
        }

        $garantie->delete();
        return ApiResponse::success(null, 'Garantie supprimée avec succès', 204);
    }
}
