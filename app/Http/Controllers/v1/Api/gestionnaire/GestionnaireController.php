<?php

namespace App\Http\Controllers\v1\Api\gestionnaire;

use App\Enums\RoleEnum;
use App\Helpers\ApiResponse;
use App\Helpers\ImageUploadHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\gestionnaire\StorePersonnelRequest;
use App\Http\Resources\UserResource;
use App\Jobs\SendCredentialsJob;
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
    public function indexPersonnels(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $currentGestionnaire = Auth::user();

        $query = Personnel::with(['user', 'user.roles'])
            ->where('id', '!=', $currentGestionnaire->personnel->id)
            ->where('gestionnaire_id', '!=', null); // Exclure le gestionnaire actuel

        // Filtre par statut actif
        if ($request->has('est_actif')) {
            $estActif = filter_var($request->input('est_actif'), FILTER_VALIDATE_BOOLEAN);
            $query->whereHas('user', function ($q) use ($estActif) {
                $q->where('est_actif', $estActif);
            });
        }

        // Filtre par sexe
        if ($request->filled('sexe')) {
            $query->where('sexe', $request->input('sexe'));
        }

        // Filtre par rôle
        if ($request->filled('role')) {
            $query->whereHas('user.roles', function ($q) use ($request) {
                $q->where('name', $request->input('role'));
            });
        }

        // Recherche globale (nom, prénoms, email)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenoms', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('email', 'like', "%{$search}%");
                  });
            });
        }

        // Tri
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $personnels = $query->paginate($perPage);

        // Transformer en UserResource
        $userCollection = $personnels->getCollection()->map(function ($personnel) {
            return $personnel->user;
        });

        $paginatedUsers = new LengthAwarePaginator(
            UserResource::collection($userCollection),
            $personnels->total(),
            $personnels->perPage(),
            $personnels->currentPage(),
            ['path' => Paginator::resolveCurrentPath()]
        );

        return ApiResponse::success($paginatedUsers, 'Liste des personnels récupérée avec succès');
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
        if (isset($validated['photo_url'])) {
            $photoUrl = ImageUploadHelper::uploadImage($validated['photo_url'], 'uploads/users/personnels');
            if (!$photoUrl) {
                return ApiResponse::error('Erreur lors de l\'upload de la photo', 422);
            }
        }

        DB::beginTransaction();

        try {
            // Créer l'utilisateur
            $user = User::create([
                'email' => $validated['email'],
                'contact' => $validated['contact'] ?? null,
                'adresse' => $validated['adresse'],
                'photo_url' => $photoUrl,
                'mot_de_passe_a_changer' => true,
                'est_actif' => false,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
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
                'gestionnaire_id' => Auth::user()->personnel->id,
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

            return ApiResponse::success([
                'personnel' => new UserResource($user->load(['roles', 'personnel'])),
            ], 'Personnel créé avec succès. Les identifiants ont été envoyés par email.', 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création du personnel: ' . $e->getMessage());
            return ApiResponse::error('Erreur lors de la création du personnel', 500);
        }
    }

    /**
     * Afficher les détails d'un personnel
     */
    public function showPersonnel($id)
    {
        $currentGestionnaire = Auth::user();

        $personnel = Personnel::with(['user', 'user.roles'])
            ->where('gestionnaire_id', $currentGestionnaire->personnel->id)
            ->where('id', '!=', $currentGestionnaire->personnel->id)
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
    public function suspendPersonnel($id)
    {
        $currentGestionnaire = Auth::user();

        $personnel = Personnel::with('user')
            ->where('gestionnaire_id', $currentGestionnaire->personnel->id)
            ->where('id', '!=', $currentGestionnaire->personnel->id)
            ->find($id);

        if (!$personnel) {
            return ApiResponse::error('Personnel non trouvé', 404);
        }

        $personnel->user->update(['est_actif' => false]);

        Log::info("Personnel suspendu - ID: {$personnel->id}, Email: {$personnel->user->email}");

        return ApiResponse::success(
            new UserResource($personnel->user), 
            'Personnel suspendu avec succès'
        );
    }

    /**
     * Réactiver un personnel
     */
    public function activatePersonnel($id)
    {
        $currentGestionnaire = Auth::user();

        $personnel = Personnel::with('user')
            ->where('gestionnaire_id', $currentGestionnaire->personnel->id)
            ->where('id', '!=', $currentGestionnaire->personnel->id)
            ->find($id);

        if (!$personnel) {
            return ApiResponse::error('Personnel non trouvé', 404);
        }

        $personnel->user->update(['est_actif' => true]);

        Log::info("Personnel réactivé - ID: {$personnel->id}, Email: {$personnel->user->email}");

        return ApiResponse::success(
            new UserResource($personnel->user), 
            'Personnel réactivé avec succès'
        );
    }

    /**
     * Supprimer définitivement un personnel
     */
    public function destroyPersonnel($id)
    {
        $currentGestionnaire = Auth::user();

        $personnel = Personnel::with('user')
            ->where('gestionnaire_id', $currentGestionnaire->personnel->id)
            ->where('id', '!=', $currentGestionnaire->personnel->id)
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
            'total' => Personnel::where('gestionnaire_id', $currentGestionnaire->personnel->id)
                ->where('id', '!=', $currentGestionnaire->personnel->id)
                ->count(),
            
            'actifs' => Personnel::where('gestionnaire_id', $currentGestionnaire->personnel->id)
                ->where('id', '!=', $currentGestionnaire->personnel->id)
                ->whereHas('user', function ($q) {
                    $q->where('est_actif', true);
                })->count(),
            
            'suspendus' => Personnel::where('gestionnaire_id', $currentGestionnaire->personnel->id)
                ->where('id', '!=', $currentGestionnaire->personnel->id)
                ->whereHas('user', function ($q) {
                    $q->where('est_actif', false);
                })->count(),
        ];

        return ApiResponse::success($stats, 'Statistiques des personnels récupérées avec succès');
    }
}
