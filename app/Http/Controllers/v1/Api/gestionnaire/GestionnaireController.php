<?php

namespace App\Http\Controllers\v1\Api\gestionnaire;

use App\Enums\RoleEnum;
use App\Helpers\ApiResponse;
use App\Helpers\ImageUploadHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\gestionnaire\StorePersonnelRequest;
use App\Http\Resources\UserResource;
use App\Jobs\SendCredentialsJob;
use App\Models\Personne;
use App\Models\Personnel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class GestionnaireController extends Controller
{
    /**
     * Lister tous les personnels gérés par le gestionnaire connecté
     */
    public function indexPersonnels()
{
    $currentUser = Auth::user();
    $currentPersonnelId = $currentUser?->personnel?->id;

    $personnels = Personnel::with(['user.roles', 'user.personne'])
        ->where('id', '!=', $currentPersonnelId) // exclure le gestionnaire connecté
        ->whereHas('user.roles', function ($q) {
            $q->whereNotIn('name', [RoleEnum::ADMIN_GLOBAL->value, RoleEnum::GESTIONNAIRE->value]);
        })->orderBy('created_at', 'desc')
        ->get()
        ->pluck('user');

    return ApiResponse::success(
        UserResource::collection($personnels),
        'Liste des personnels récupérée avec succès'
    );
}

    /**
     * Créer un nouveau personnel
     */
    public function storePersonnel(StorePersonnelRequest $request)
    {
        $validated = $request->validated();
        $password = User::genererMotDePasse();
        $photoUrl = null;

        // Gestion de l'upload de la photo
        if (isset($validated['photo'])) {
            $photoUrl = ImageUploadHelper::uploadImage($validated['photo'], 'uploads/users/personnels/'.$validated['email'].'/');
            if (!$photoUrl) {
                return ApiResponse::error('Erreur lors de l\'upload de la photo', 422);
            }
        }

        DB::beginTransaction();

        try {
            // Créer d'abord la personne
            $personne = Personne::create([
                'nom' => $validated['nom'],
                'prenoms' => $validated['prenoms'] ?? null,
                'date_naissance' => $validated['date_naissance'] ?? null,
                'sexe' => $validated['sexe'] ?? null,
                'profession' => $validated['profession'] ?? null,
            ]);

            // Créer l'utilisateur avec le personne_id
            $user = User::create([
                'email' => $validated['email'],
                'contact' => $validated['contact'] ?? null,
                'adresse' => $validated['adresse'],
                'photo_url' => $photoUrl,
                'mot_de_passe_a_changer' => true,
                'est_actif' => false,
                'password' => Hash::make($password),
                'email_verifier_a' => now(),
                'personne_id' => $personne->id,
            ]);

            // Assigner le rôle
            $user->assignRole($validated['role']);

            // Créer le personnel
            $personnelData = [
                'user_id' => $user->id,
                'nom' => $validated['nom'],
                'prenoms' => $validated['prenoms'] ?? null,
                'sexe' => $validated['sexe'] ?? null,
                'date_naissance' => $validated['date_naissance'] ?? null,
                'gestionnaire_id' => Auth::user()->id,
            ];

            // Générer un code de parrainage pour les commerciaux
            if ($validated['role'] === RoleEnum::COMMERCIAL->value) {
                $personnelData['code_parainage'] = Personnel::genererCodeParainage();
            }

            $personnel = Personnel::create($personnelData);

            // Envoyer les identifiants par email
            dispatch(new SendCredentialsJob($user, $password));
            
            Log::info("Personnel créé - Email: {$user->email}, Mot de passe: {$password}");

            DB::commit();

            return ApiResponse::success(null , RoleEnum::getLabel($validated['role']).' créé avec succès. Les identifiants ont été envoyés par email.', 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création du personnel: ' . $e->getMessage());
            return ApiResponse::error('Erreur lors de la création du personnel', 500);
        }
    }

    /**
     * Afficher les détails d'un personnel
     */
    public function showPersonnel(int $id)
    {
        $currentGestionnaire = Auth::user();

        $personnel = Personnel::with(['user', 'user.roles'])
        ->where('id', '!=', $currentGestionnaire->personnel->id)
        ->whereNotNull('gestionnaire_id')
        ->find($id);

        if (!$personnel) {
            return ApiResponse::error('Personnel non trouvé', 404);
        }

        return ApiResponse::success(
            new UserResource($personnel->user), 
            'Détails du personnel'
        );
    }

    /**
     * Suspendre/désactiver un personnel
     */
    public function togglePersonnelStatus($id)
    {
        $currentGestionnaire = Auth::user();
        $personnel = Personnel::with(['user', 'user.roles'])
        ->where('id', '!=', $currentGestionnaire->personnel->id)
        ->whereNotNull('gestionnaire_id')
        ->find($id);

        if (!$personnel) {
            return ApiResponse::error('Personnel non trouvé', 404);
        }

        if($personnel->user->mot_de_passe_a_changer) {
            return ApiResponse::error('Le personnel doit changer son mot de passe avant de pouvoir être suspendu', 400, null);
        }

        $personnel->user->update(['est_actif' => !$personnel->user->est_actif]);

        Log::info("Personnel suspendu - ID: {$personnel->id}, Email: {$personnel->user->email}");

        return ApiResponse::success(null, 'Personnel ' . ($personnel->user->est_actif ? 'suspendu' : 'réactivé') . ' avec succès');
    }

    /**
     * Supprimer définitivement un personnel
     */
    public function destroyPersonnel($id)
    {
        $currentGestionnaire = Auth::user();
        $personnel = Personnel::with(['user', 'user.roles'])
        ->where('id', '!=', $currentGestionnaire->personnel->id)
        ->whereNotNull('gestionnaire_id')
        ->find($id);

        if (!$personnel) {
            return ApiResponse::error('Personnel non trouvé', 404);
        }

        DB::beginTransaction();

        try {
            // Supprimer le personnel (cascade vers user)
            $personnel->delete();
            
            Log::info("Personnel supprimé - ID: {$personnel->id}, Email: {$personnel->user->email}");

            DB::commit();

            return ApiResponse::success(null, 'Personnel supprimé avec succès', 204);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression du personnel: ' . $e->getMessage());
            return ApiResponse::error('Erreur lors de la suppression du personnel', 500);
        }
    }

    /**
     * Statistiques des personnels gérés par le gestionnaire
     */
    public function personnelStats()
    {
        $currentGestionnaire = Auth::user();

        $stats = [
            'total' => Personnel::with(['user', 'user.roles'])
            ->where('id', '!=', $currentGestionnaire->personnel->id)
            ->whereNotNull('gestionnaire_id')
            ->count(),
            
            'actifs' => Personnel::with(['user', 'user.roles'])
            ->where('id', '!=', $currentGestionnaire->personnel->id)
            ->whereNotNull('gestionnaire_id')
            ->whereHas('user', function ($q) {
                $q->where('est_actif', true);
            })->count(),
            
            'inactifs' => Personnel::with(['user', 'user.roles'])
            ->where('id', '!=', $currentGestionnaire->personnel->id)
            ->whereNotNull('gestionnaire_id')
            ->whereHas('user', function ($q) {
                $q->where('est_actif', false);
            })->count(),
            
            'repartition_par_role' => Personnel::with(['user', 'user.roles'])
            ->where('id', '!=', $currentGestionnaire->personnel->id)
            ->whereNotNull('gestionnaire_id')
            ->whereHas('user.roles')
            ->get()
                ->groupBy(function ($personnel) {
                    return $personnel->user->roles->first()->name ?? 'Aucun rôle';
                })
                ->map(function ($group) {
                    return $group->count();
                }),
            
            'repartition_par_sexe' => Personnel::with(['user', 'user.roles'])
            ->where('id', '!=', $currentGestionnaire->personnel->id)
            ->whereNotNull('gestionnaire_id')
            ->get()
                ->groupBy(function ($personnel) {
                    return optional($personnel->user->personne)->sexe ?? 'Non spécifié';
                })
                ->map(function ($group) {
                    return $group->count();
                }),
        ];

        return ApiResponse::success($stats, 'Statistiques des personnels récupérées avec succès');
    }
}
