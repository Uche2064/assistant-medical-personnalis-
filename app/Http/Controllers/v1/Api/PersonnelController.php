<?php

namespace App\Http\Controllers\v1\Api;

use App\Enums\RoleEnum;
use App\Helpers\ApiResponse;
use Illuminate\Routing\Controller as BaseController;
use App\Http\Requests\gestionnaire\PersonnelFormRequest;
use App\Models\Commercial;
use App\Models\Personnel;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class PersonnelController extends BaseController
{

    protected $notificationService;
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {

        $perPage = $request->input('per_page', 10);
        $query = Personnel::with('user');


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


    /**
     * Store a newly created resource in storage.
     */
    public function store(PersonnelFormRequest $request)
    {

        // Récupérer les données validées
        $data = $request->validated();

        $gestionnaire = Auth::user()->gestionnaire;

        try {
            DB::beginTransaction();

            // Générer un mot de passe aléatoire
            $password = User::genererMotDePasse();

            // Création de l'utilisateur
            $user = User::create([
                'nom' => $data['nom'],
                'prenoms' => $data['prenoms'] ?? null,
                'email' => $data['email'],
                'contact' => $data['contact'] ?? null,
                'adresse' => $data['adresse'],
                'date_naissance' => $data['date_naissance'] ?? null,
                'sexe' => $data['sexe'] ?? null,
                'photo_url' => $data['photo'] ?? null,
                'est_actif' => false,
                'role' => $data['role'],
                'mot_de_passe_a_changer' => true,
                'password' => Hash::make($password),
            ]);

            // Assigner le rôle de personnel
            $user->assignRole($data['role']);

            // Création du personnel
            if($data['role'] == RoleEnum::COMMERCIAL->value){
                $personnel = Personnel::create([
                    'user_id' => $user->id,
                    'code_parainage' => Personnel::genererCodeParainage(),
                    'gestionnaire_id' => $gestionnaire->id
                ]);
            } else {
                $personnel = Personnel::create([
                    'user_id' => $user->id,
                    'gestionnaire_id' => $gestionnaire->id,
                ]);
            }

            DB::commit();

            $this->notificationService->sendCredentials($user, $password);

            // On retourne le mot de passe généré pour que le gestionnaire puisse le communiquer
            return ApiResponse::success([
                'personnel' => $personnel->load('user'),
                'password' => $password,  // En production, envoyer par email ou SMS
            ], 'Personnel créé avec succès', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Une erreur est survenue lors de la création du personnel: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

        $personnel = Personnel::with('user')
            ->where('id', $id)
            ->first();

        if (!$personnel) {
            return ApiResponse::error('Personnel non trouvé', 404);
        }

        return ApiResponse::success($personnel, 'Personnel récupéré avec succès');
    }

    /**
     * Update the specified resource in storage.
     */
    // public function update(PersonnelFormRequest $request, string $id)
    // {
    //     // Récupérer les données validées
    //     $data = $request->validated();



    //     $personnel = Personnel::where('id', $id)
    //         ->first();

    //     if (!$personnel) {
    //         return ApiResponse::error('Personnel non trouvé', 404);
    //     }

    //     try {
    //         DB::beginTransaction();

    //         // Mettre à jour l'utilisateur
    //         $personnel->user->update([
    //             'nom' => $data['nom'] ?? $personnel->user->nom,
    //             'prenoms' => $data['prenoms'] ?? $personnel->user->prenoms,
    //             'email' => $data['email'] ?? $personnel->user->email,
    //             'contact' => $data['contact'] ?? $personnel->user->contact,
    //             'adresse' => $data['adresse'] ?? $personnel->user->adresse,
    //             'date_naissance' => $data['date_naissance'] ?? $personnel->user->date_naissance,
    //             'sexe' => $data['sexe'] ?? $personnel->user->sexe,
    //             'photo' => $data['photo'] ?? $personnel->user->photo,
    //             'username' => $data['username'] ?? $personnel->user->username,
    //         ]);

    //         // Mettre à jour le personnel
    //         $personnel->update([
    //             'type_personnel' => $data['type_personnel'] ?? $personnel->type_personnel,
    //         ]);

    //         DB::commit();

    //         return ApiResponse::success(
    //             $personnel->fresh()->load('user'),
    //             'Personnel mis à jour avec succès'
    //         );
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return ApiResponse::error('Une erreur est survenue lors de la mise à jour du personnel: ' . $e->getMessage(), 500);
    //     }
    // }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        $personnel = Personnel::where('id', $id)
            ->first();

        if (!$personnel) {
            return ApiResponse::error('Personnel non trouvé', 404);
        }

        try {
            DB::beginTransaction();

            // Supprimer le personnel (soft delete)
            $personnel->delete();

            // Désactiver l'utilisateur
            $personnel->user->update(['est_actif' => false]);

            DB::commit();

            return ApiResponse::success(null, 'Personnel supprimé avec succès', 204);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Une erreur est survenue lors de la suppression du personnel: ' . $e->getMessage(), 500);
        }
    }
}
