<?php

namespace App\Http\Controllers\v1\Api;

use App\Enums\RoleEnum;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\gestionnaire\PersonnelFormRequest;
use App\Models\Personnel;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PersonnelController extends Controller
{

    protected $notificationService;
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Récupérer tous les personnels ajouter par ce gestionnaire
        $personnels = Personnel::with('user')
            ->where('gestionnaire_id', Auth::user()->gestionnaire->id)
            ->get();
            
        if ($personnels->isEmpty()) {
            return ApiResponse::success([], 'Aucun personnel trouvé');
        }

        return ApiResponse::success($personnels, 'Liste des personnels récupérée avec succès');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PersonnelFormRequest $request)
    {
        // Récupérer les données validées
        $data = $request->validated();
        
        // Récupérer l'ID et la compagnie du gestionnaire connecté
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
                'photo' => $data['photo'] ?? null,
                'username' => $data['username'] ?? null,
                'must_change_password' => true,
                'password' => bcrypt($password),
            ]);
            
            // Assigner le rôle de personnel
            $user->assignRole(RoleEnum::PERSONNEL->value);
            
            // Création du personnel
            $personnel = Personnel::create([
                'user_id' => $user->id,
                'type_personnel' => $data['type_personnel'],
                'gestionnaire_id' => $gestionnaire->id
            ]);
            
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
        // Récupérer l'ID de la compagnie du gestionnaire connecté
        $compagnieId = Auth::user()->gestionnaire->compagnie_id;
        
        // Récupérer le personnel avec l'ID spécifié et appartenant à la compagnie
        $personnel = Personnel::with('user')
            ->where('id', $id)
            ->where('compagnie_id', $compagnieId)
            ->first();
            
        if (!$personnel) {
            return ApiResponse::error('Personnel non trouvé', 404);
        }
        
        return ApiResponse::success($personnel, 'Personnel récupéré avec succès');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PersonnelFormRequest $request, string $id)
    {
        // Récupérer les données validées
        $data = $request->validated();
        
        // Récupérer l'ID de la compagnie du gestionnaire connecté
        $compagnieId = Auth::user()->gestionnaire->compagnie_id;
        
        // Récupérer le personnel avec l'ID spécifié et appartenant à la compagnie
        $personnel = Personnel::where('id', $id)
            ->where('compagnie_id', $compagnieId)
            ->first();
            
        if (!$personnel) {
            return ApiResponse::error('Personnel non trouvé', 404);
        }
        
        try {
            DB::beginTransaction();
            
            // Mettre à jour l'utilisateur
            $personnel->user->update([
                'nom' => $data['nom'] ?? $personnel->user->nom,
                'prenoms' => $data['prenoms'] ?? $personnel->user->prenoms,
                'email' => $data['email'] ?? $personnel->user->email,
                'contact' => $data['contact'] ?? $personnel->user->contact,
                'adresse' => $data['adresse'] ?? $personnel->user->adresse,
                'date_naissance' => $data['date_naissance'] ?? $personnel->user->date_naissance,
                'sexe' => $data['sexe'] ?? $personnel->user->sexe,
                'photo' => $data['photo'] ?? $personnel->user->photo,
                'username' => $data['username'] ?? $personnel->user->username,
            ]);
            
            // Mettre à jour le personnel
            $personnel->update([
                'type_personnel' => $data['type_personnel'] ?? $personnel->type_personnel,
            ]);
            
            DB::commit();
            
            return ApiResponse::success(
                $personnel->fresh()->load('user'), 
                'Personnel mis à jour avec succès'
            );
            
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Une erreur est survenue lors de la mise à jour du personnel: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Récupérer l'ID de la compagnie du gestionnaire connecté
        $compagnieId = Auth::user()->gestionnaire->compagnie_id;
        
        // Récupérer le personnel avec l'ID spécifié et appartenant à la compagnie
        $personnel = Personnel::where('id', $id)
            ->where('compagnie_id', $compagnieId)
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
