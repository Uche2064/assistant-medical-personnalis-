<?php

namespace App\Http\Controllers\v1\Api\admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\admin\GestionnaireFormRequest;
use App\Models\Compagnie;
use App\Models\Gestionnaire;
use App\Models\User;
use Illuminate\Http\Request;

class GestionnaireController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $gestionnaires = Gestionnaire::with('compagnie')->get();

        if($gestionnaires->isEmpty()) {
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
        // créer l'utilisateur
        $user = User::create([
            'nom' => $data['nom'],
            'prenoms' => $data['prenoms'],
            'email' => $data['email'],
            'contact' => $data['contact'],
            'adresse' => $data['adresse'],
            'date_naissance' => $data['date_naissance'],
            'sexe' => $data['sexe'],
            'photo' => $data['photo'],
            'username' => $data['username'],
            'must_change_password' => true,
            'password' => bcrypt($password),
        ]);

        $user->save();

        // get le id
        $user_id = $user->id;


        // vérifier si la compagnie existe
        if($data['compagnie_id']) {
            $compagnie = Compagnie::find($data['compagnie_id']);
            if(!$compagnie) {
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
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
