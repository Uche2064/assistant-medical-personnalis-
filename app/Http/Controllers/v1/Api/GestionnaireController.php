<?php

namespace App\Http\Controllers\v1\Api;

use App\Enums\RoleEnum;
use App\Events\GestionnaireCreated;
use App\Helpers\ApiResponse;
use App\Helpers\ImageUploadHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\admin\GestionnaireFormRequest;
use App\Http\Requests\admin\GestionnaireUpdateFormRequest;
use App\Models\Gestionnaire;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Throwable;

class GestionnaireController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }


    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $query = Gestionnaire::with('user', 'user.roles');


        // 🔍 Recherches sur les colonnes de l'utilisateur
        $query->whereHas('user', function ($q) use ($request) {
            if (!empty($request->input('sexe'))) {
                $q->where('sexe', $request->sexe);
            }

            if (!empty($request->input('est_actif'))) {
                $q->where('est_actif', filter_var($request->est_actif, FILTER_VALIDATE_BOOLEAN));
            }

            

            if (!empty($request->filled('search'))) {
                $search = $request->search;
                $q->where(function ($q2) use ($search) {
                    $q2->where('nom', 'like', "%{$search}%")
                        ->orWhere('prenoms', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            }
        });

        $gestionnaires = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return ApiResponse::success($gestionnaires, 'Liste des gestionnaires récupérée avec succès');
    }


    public function store(GestionnaireFormRequest $request)
    {
        try {
            $data = $request->validated();
            $password = User::genererMotDePasse();
            $photoUrl = null;
            if (isset($data['photo_url'])) {
                $photo = $data['photo_url'];
                $photoUrl = ImageUploadHelper::uploadImage($photo, 'uploads/users');
            } else {
                $photo = null;
            }

            // créer l'user
            $user = User::create([
                'nom' => $data['nom'],
                'prenoms' => $data['prenoms'] ?? null,
                'email' => $data['email'],
                'contact' => $data['contact'] ?? null,
                'adresse' => $data['adresse'],
                'date_naissance' => $data['date_naissance'] ?? null,
                'sexe' => $data['sexe'] ?? null,
                'photo_url' => $photoUrl ?? null,
                'mot_de_passe_a_changer' => true,
                'est_actif' => false,
                'password' => Hash::make($password),
            ]);

            $user->assignRole(RoleEnum::GESTIONNAIRE->value);
            $user->save();
            Gestionnaire::create([
                'user_id' => $user->id,
            ]);

            $this->notificationService->sendCredentials($user, $password);

            $reponseData = [
                "gestionnaire" => $user,
                "password" => $password
            ];
            return ApiResponse::success($reponseData, 'Un email de confirmation a été envoyé à l\'adresse email fournie.', 201);
        } catch (Throwable $th) {
            return ApiResponse::error('Une erreur est survenue lors de la création du gestionnaire', 500, $th->getMessage());
        }
    }



    public function show(int $id)
    {
        try {
            $gestionnaire = Gestionnaire::with('user')->find($id);
            if (!$gestionnaire) {
                return ApiResponse::error('Gestionnaire non trouvé', 404);
            }

            $data = [
                'id' => $gestionnaire->id,
                'nom' => $gestionnaire->user->nom,
                'prenoms' => $gestionnaire->user->prenoms,
                'email' => $gestionnaire->user->email,
                'contact' => $gestionnaire->user->contact,
                'username' => $gestionnaire->user->username,
                'adresse' => $gestionnaire->user->adresse,
                'sexe' => $gestionnaire->user->sexe,
                'date_naissance' => $gestionnaire->user->date_naissance,
                'photo' => $gestionnaire->user->photo,
                'est_actif' => $gestionnaire->user->est_actif,
                'must_change_password' => $gestionnaire->user->must_change_password,
                'role' => $gestionnaire->user->getRoleNames()->first(),
            ];
            return ApiResponse::success($data, 'Gestionnaire récupéré avec succès');
        } catch (\Throwable $th) {
            return ApiResponse::error('Une erreur est survenue lors de la récupération du gestionnaire', 500, $th->getMessage());
        }
    }


    // public function update(GestionnaireUpdateFormRequest $request, int $id)
    // {
    //     $gestionnaire = Gestionnaire::with('user')->find($id);
    //     if (!$gestionnaire) {
    //         return ApiResponse::error('Gestionnaire non trouvé', 404);
    //     }

    //     $data = $request->validated();

    //     // Met à jour les champs user
    //     if ($gestionnaire->user) {
    //         $gestionnaire->user->fill($data);
    //         $gestionnaire->user->save();
    //     }

    //     // Met à jour les champs gestionnaire
    //     $gestionnaire->fill($data);
    //     $gestionnaire->save();

    //     $gestionnaire->load(['user']);
    //     $data = [
    //         'id' => $gestionnaire->id,
    //         'nom' => $gestionnaire->user->nom,
    //         'prenoms' => $gestionnaire->user->prenoms,
    //         'email' => $gestionnaire->user->email,
    //         'contact' => $gestionnaire->user->contact,
    //         'username' => $gestionnaire->user->username,
    //         'adresse' => $gestionnaire->user->adresse,
    //         'sexe' => $gestionnaire->user->sexe,
    //         'date_naissance' => $gestionnaire->user->date_naissance,
    //         'photo' => $gestionnaire->user->photo,
    //         'est_actif' => $gestionnaire->user->est_actif,
    //         'must_change_password' => $gestionnaire->user->must_change_password,
    //         'role' => $gestionnaire->user->getRoleNames()->first(),
    //     ];
    //     return ApiResponse::success($data, 'Gestionnaire mis à jour avec succès');
    // }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $gestionnaire = Gestionnaire::with('user')->find($id);
        if (!$gestionnaire) {
            return ApiResponse::error('Gestionnaire non trouvé', 404);
        }
        $gestionnaire->delete();
        return ApiResponse::success(null, 'Gestionnaire supprimé avec succès', 204);
    }


   
}
