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
            ->whereNotNull('gestionnaire_id')
    
            // Filtre par statut actif
            ->when($request->has('est_actif'), function ($q) use ($request) {
                $estActif = filter_var($request->input('est_actif'), FILTER_VALIDATE_BOOLEAN);
                $q->whereHas('user', fn ($subQ) => $subQ->where('est_actif', $estActif));
            })
    
            // Filtre par sexe
            ->when($request->filled('sexe'), function ($q) use ($request) {
                $q->where('sexe', $request->input('sexe'));
            })
    
            // Filtre par rôle
            ->when($request->filled('role'), function ($q) use ($request) {
                $q->whereHas('user.roles', fn ($subQ) => $subQ->where('name', $request->input('role')));
            })
    
            // Recherche globale (nom, prénoms, email)
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->input('search');
                $q->where(function ($subQ) use ($search) {
                    $subQ->where('nom', 'like', "%{$search}%")
                        ->orWhere('prenoms', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($userQ) => $userQ->where('email', 'like', "%{$search}%"));
                });
            });
    
        // Tri
        $query->orderBy(
            $request->input('sort_by', 'created_at'),
            $request->input('sort_order', 'desc')
        );
    
        $personnels = $query->paginate($perPage);
    
        // Mapper en UserResource
        $userCollection = $personnels->getCollection()->map(fn ($personnel) => $personnel->user);
    
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
        if (isset($validated['photo'])) {
            $photoUrl = ImageUploadHelper::uploadImage($validated['photo'], 'uploads/users/personnels/'.$validated['email'].'/');
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
                'photo' => $photoUrl,
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
            ->selectRaw('sexe, COUNT(*) as count')
            ->groupBy('sexe')
            ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->sexe ?? 'Non spécifié' => $item->count];
                }),
        ];

        return ApiResponse::success($stats, 'Statistiques des personnels récupérées avec succès');
    }
}
