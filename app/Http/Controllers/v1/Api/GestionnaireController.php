<?php

namespace App\Http\Controllers\v1\Api;

use App\Enums\RoleEnum;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\admin\GestionnaireFormRequest;
use App\Http\Requests\admin\GestionnaireUpdateFormRequest;
use App\Models\Gestionnaire;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class GestionnaireController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

  
    public function index()
    {
        $gestionnaires = Gestionnaire::with('compagnie')->get();

        if ($gestionnaires->isEmpty()) {
            return ApiResponse::success($gestionnaires, 'Aucun gestionnaire trouvé');
        }
        return ApiResponse::success($gestionnaires, 'Liste des gestionnaires récupérée avec succès');
    }

    
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
            'username' => $data['username'] ?? null,
            'must_change_password' => true,
            'password' => bcrypt($password),
        ]);

        $user->assignRole(RoleEnum::GESTIONNAIRE);
        $user->save();

        $user_id = $user->id;

      $gestionnaire = Gestionnaire::create([
            'user_id' => $user_id,
        ]);

        // envoyé un mail au gestionnaire

        $this->notificationService->sendCredentials($user, $password);

        // préparer les données à renvoyé
        $reponseData = [
            'gestionnaire' => [
                'nom' => $user->nom,
                'prenoms' => $user->prenoms,
                'email' => $user->email,
                'must_change_password' => $user->must_change_password,
            ],
            'password' => $password
        ];
        return ApiResponse::success($reponseData, 'Un email de confirmation a été envoyé à l\'adresse email fournie.');
    }



    public function show(int $id)
    {
        $gestionnaire = Gestionnaire::with('user')->find($id);
        if (!$gestionnaire) {
            return ApiResponse::error('Gestionnaire non trouvé', 404);
        }
        return ApiResponse::success($gestionnaire, 'Gestionnaire récupéré avec succès');
    }


    public function update(GestionnaireUpdateFormRequest $request, int $id)
    {
        $gestionnaire = Gestionnaire::with('user')->find($id);
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

        $gestionnaire->load(['user']);
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
            'role' => $gestionnaire->user->getRoleNames()->first(),
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

    public function gestionnaireByCompagnieId(Request $request) {
        $compagnie_id = $request->compagnie_id;
        $gestionnaires = Gestionnaire::with('user')->where('compagnie_id', $compagnie_id)->get();
        if ($gestionnaires->isEmpty()) {
            return ApiResponse::success($gestionnaires, 'Aucun gestionnaire trouvé');
        }
        $data = [];
        foreach ($gestionnaires as $gestionnaire) {
            $data[] = [
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
                'role' => $gestionnaire->user->getRoleNames()->first(),
            ];
        }
        return ApiResponse::success($data, 'Liste des gestionnaires récupérée avec succès');
    }
}
