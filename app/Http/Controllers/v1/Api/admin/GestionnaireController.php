<?php

namespace App\Http\Controllers\v1\Api\admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\admin\GestionnaireFormRequest;
use App\Http\Requests\admin\GestionnaireUpdateFormRequest;
use App\Models\Compagnie;
use App\Models\Gestionnaire;
use App\Models\User;

class GestionnaireController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $gestionnaires = Gestionnaire::with('compagnie')->get();

        if ($gestionnaires->isEmpty()) {
            return ApiResponse::success($gestionnaires, 'Aucun gestionnaire trouvé');
        }
        return ApiResponse::success($gestionnaires, 'Liste des gestionnaires récupérée avec succès');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(GestionnaireFormRequest $request)
    {
        $data = $request->validated();
        $password = User::genererMotDePasse();
        // créer l'user
        $user = User::create([
            'nom' => $data['nom'],
            'prenoms' => $data['prenoms'] ?? null,
            'email' => $data['email'] ?? null,
            'contact' => $data['contact'] ?? null,
            'adresse' => $data['adresse'] ?? null,
            'date_naissance' => $data['date_naissance'] ?? null,
            'sexe' => $data['sexe'] ?? null,
            'photo' => $data['photo'] ?? null,
            'username' => $data['username'],
            'must_change_password' => true,
            'password' => bcrypt($password),
        ]);

        $user->save();

        // get le id
        $user_id = $user->id;


        // vérifier si la compagnie existe
        if ($data['compagnie_id']) {
            $compagnie = Compagnie::find($data['compagnie_id']);
            if (!$compagnie) {
                return ApiResponse::error('Compagnie non trouvée', 404, 'compagnie-non-trouve');
            }
        }

        // enregistre le gestionnaire
        $gestionnaire = Gestionnaire::create([
            'user_id' => $user_id,
            'compagnie_id' => $data['compagnie_id'],
        ]);

        // préparer les données à renvoyé
        $reponseData = [
            'gestionnaire' => [
                'id' => $user->id,
                'nom' => $user->nom,
                'prenoms' => $user->prenoms,
                'email' => $user->email,
                'contact' => $user->contact,
                'username' => $user->username,
                'adresse' => $user->adresse,
                'sexe' => $user->sexe,
                'date_naissance' => $user->date_naissance,
                'photo' => $user->photo,
                'est_actif' => $user->est_actif,
                'must_change_password' => $user->must_change_password,
            ],
            'password' => $password
        ];
        return ApiResponse::success($reponseData, 'Gestionnaire créé avec succès');
    }


    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $gestionnaire = Gestionnaire::with('compagnie')->find($id);
        if (!$gestionnaire) {
            return ApiResponse::error('Gestionnaire non trouvé', 404);
        }
        return ApiResponse::success($gestionnaire, 'Gestionnaire récupéré avec succès');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(GestionnaireUpdateFormRequest $request, int $id)
    {
        $gestionnaire = Gestionnaire::with('user', 'compagnie')->find($id);
        if (!$gestionnaire) {
            return ApiResponse::error('Gestionnaire non trouvé', 404);
        }

        $data = $request->validated();

        // Met à jour les champs user
        if ($gestionnaire->user) {
            $gestionnaire->user->fill($data);
            $gestionnaire->user->save();
        }

        // Met à jour les champs gestionnaire
        $gestionnaire->fill($data);
        $gestionnaire->save();

        $gestionnaire->load(['user', 'compagnie']);
        $data = [
            'id' => $gestionnaire->id,
            'nom' => $gestionnaire->user->nom ?? null,
            'prenoms' => $gestionnaire->user->prenoms ?? null,
            'email' => $gestionnaire->user->email ?? null,
            'contact' => $gestionnaire->user->contact ?? null,
            'username' => $gestionnaire->user->username ?? null,
            'adresse' => $gestionnaire->user->adresse ?? null,
            'sexe' => $gestionnaire->user->sexe ?? null,
            'date_naissance' => $gestionnaire->user->date_naissance ?? null,
            'photo' => $gestionnaire->user->photo ?? null,
            'est_actif' => $gestionnaire->user->est_actif ?? null,
            'must_change_password' => $gestionnaire->user->must_change_password ?? null,
            'compagnie' => $gestionnaire->compagnie,
        ];
        return ApiResponse::success($data, 'Gestionnaire mis à jour avec succès');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $gestionnaire = Gestionnaire::with('compagnie')->find($id);
        if (!$gestionnaire) {
            return ApiResponse::error('Gestionnaire non trouvé', 404);
        }
        $gestionnaire->delete();
        return ApiResponse::success(null, 'Gestionnaire supprimé avec succès', 204);
    }
}
