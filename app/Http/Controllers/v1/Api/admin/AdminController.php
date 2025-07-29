<?php

namespace App\Http\Controllers\v1\Api\admin;

use App\Enums\RoleEnum;
use App\Helpers\ApiResponse;
use App\Helpers\ImageUploadHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\admin\StoreGestionnaireRequest;
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

class AdminController extends Controller
{
    /**
     * Créer un nouveau gestionnaire
     */
    public function storeGestionnaire(StoreGestionnaireRequest $request)
    {
        $validated = $request->validated();
        $password = User::genererMotDePasse();
        $photoUrl = null;

        // Gestion de l'upload de la photo
        if (isset($validated['photo_url'])) {
            $photoUrl = ImageUploadHelper::uploadImage($validated['photo_url'], 'uploads/users/gestionnaires');
            if (!$photoUrl) {
                return ApiResponse::error('Erreur lors de l\'upload de la photo', 422);
            }
        }

        DB::beginTransaction();

        try {
            // Créer l'utilisateur
            $user = User::create([
                'email' => $validated['email'],
                'contact' => $validated['contact'],
                'adresse' => $validated['adresse'] ?? null,
                'password' => Hash::make($password),
                'est_actif' => false,
                'mot_de_passe_a_changer' => true,
                'email_verified_at' => now(),
                'photo_url' => $photoUrl,
            ]);

            // Assigner le rôle gestionnaire
            $user->assignRole(RoleEnum::GESTIONNAIRE->value);

            // Créer le personnel gestionnaire
            $gestionnaire = Personnel::create([
                'user_id' => $user->id,
                'nom' => $validated['nom'],
                'prenoms' => $validated['prenoms'] ?? null,
                'sexe' => $validated['sexe'] ?? null,
                'date_naissance' => $validated['date_naissance'] ?? null,
            ]);

            // Envoyer les identifiants par email
            dispatch(new SendCredentialsJob($user, $password));
            
            Log::info("Gestionnaire créé - Email: {$user->email}, Mot de passe: {$password}");

            DB::commit();

            return ApiResponse::success([
                'gestionnaire' => new UserResource($user->load(['roles', 'personnel'])),
            ], 'Gestionnaire créé avec succès. Les identifiants ont été envoyés par email.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création du gestionnaire: ' . $e->getMessage());
            return ApiResponse::error('Erreur lors de la création du gestionnaire', 500);
        }
    }

    /**
     * Lister tous les gestionnaires avec filtres
     */
    public function indexGestionnaires(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $currentAdmin = Auth::user();

        $query = Personnel::with(['user', 'user.roles'])
            ->whereHas('user', function ($q) {
                $q->whereHas('roles', function ($roleQuery) {
                    $roleQuery->where('name', RoleEnum::GESTIONNAIRE->value);
                });
            })
            ->where('id', '!=', $currentAdmin->personnel->id); // Exclure l'admin actuel

        // 🔍 Filtre par statut actif
        if ($request->has('est_actif')) {
            $estActif = filter_var($request->input('est_actif'), FILTER_VALIDATE_BOOLEAN);
            $query->whereHas('user', function ($q) use ($estActif) {
                $q->where('est_actif', $estActif);
            });
        }

        // 🔍 Filtre par sexe
        if ($request->filled('sexe')) {
            $query->where('sexe', $request->input('sexe'));
        }

        // 🔍 Recherche globale (nom, prénoms, email)
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

        // 🔍 Tri
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $gestionnaires = $query->paginate($perPage);

        // Transformer en UserResource
        $userCollection = $gestionnaires->getCollection()->map(function ($personnel) {
            return $personnel->user;
        });

        $paginatedUsers = new LengthAwarePaginator(
            UserResource::collection($userCollection),
            $gestionnaires->total(),
            $gestionnaires->perPage(),
            $gestionnaires->currentPage(),
            ['path' => Paginator::resolveCurrentPath()]
        );

        return ApiResponse::success($paginatedUsers, 'Liste des gestionnaires récupérée avec succès');
    }

    /**
     * Afficher les détails d'un gestionnaire
     */
    public function showGestionnaire($id)
    {
        $gestionnaire = Personnel::with(['user', 'user.roles'])
            ->whereHas('user', function ($q) {
                $q->whereHas('roles', function ($roleQuery) {
                    $roleQuery->where('name', RoleEnum::GESTIONNAIRE->value);
                });
            })
            ->find($id);

        if (!$gestionnaire) {
            return ApiResponse::error('Gestionnaire non trouvé', 404);
        }

        return ApiResponse::success(
            new UserResource($gestionnaire->user), 
            'Détails du gestionnaire'
        );
    }

    /**
     * Suspendre/désactiver un gestionnaire
     */
    public function suspendGestionnaire($id)
    {
        $gestionnaire = Personnel::with('user')
            ->whereHas('user', function ($q) {
                $q->whereHas('roles', function ($roleQuery) {
                    $roleQuery->where('name', RoleEnum::GESTIONNAIRE->value);
                });
            })
            ->find($id);

        if (!$gestionnaire) {
            return ApiResponse::error('Gestionnaire non trouvé', 404);
        }

        // Empêcher l'admin de se suspendre lui-même
        if ($gestionnaire->id === Auth::user()->personnel->id) {
            return ApiResponse::error('Vous ne pouvez pas suspendre votre propre compte', 403);
        }

        $gestionnaire->user->update(['est_actif' => false]);

        Log::info("Gestionnaire suspendu - ID: {$gestionnaire->id}, Email: {$gestionnaire->user->email}");

        return ApiResponse::success(
            new UserResource($gestionnaire->user), 
            'Gestionnaire suspendu avec succès'
        );
    }

    /**
     * Réactiver un gestionnaire
     */
    public function activateGestionnaire($id)
    {
        $gestionnaire = Personnel::with('user')
            ->whereHas('user', function ($q) {
                $q->whereHas('roles', function ($roleQuery) {
                    $roleQuery->where('name', RoleEnum::GESTIONNAIRE->value);
                });
            })
            ->find($id);

        if (!$gestionnaire) {
            return ApiResponse::error('Gestionnaire non trouvé', 404);
        }

        $gestionnaire->user->update(['est_actif' => true]);

        Log::info("Gestionnaire réactivé - ID: {$gestionnaire->id}, Email: {$gestionnaire->user->email}");

        return ApiResponse::success(
            new UserResource($gestionnaire->user), 
            'Gestionnaire réactivé avec succès'
        );
    }

    /**
     * Supprimer définitivement un gestionnaire
     */
    public function destroyGestionnaire($id)
    {
        $gestionnaire = Personnel::with('user')
            ->whereHas('user', function ($q) {
                $q->whereHas('roles', function ($roleQuery) {
                    $roleQuery->where('name', RoleEnum::GESTIONNAIRE->value);
                });
            })
            ->find($id);

        if (!$gestionnaire) {
            return ApiResponse::error('Gestionnaire non trouvé', 404);
        }

        // Empêcher l'admin de se supprimer lui-même
        if ($gestionnaire->id === Auth::user()->personnel->id) {
            return ApiResponse::error('Vous ne pouvez pas supprimer votre propre compte', 403);
        }

        DB::beginTransaction();

        try {
            // Supprimer le personnel (cascade vers user)
            $gestionnaire->delete();
            
            Log::info("Gestionnaire supprimé - ID: {$gestionnaire->id}, Email: {$gestionnaire->user->email}");

            DB::commit();

            return ApiResponse::success(null, 'Gestionnaire supprimé avec succès', 204);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression du gestionnaire: ' . $e->getMessage());
            return ApiResponse::error('Erreur lors de la suppression du gestionnaire', 500);
        }
    }

    /**
     * Statistiques des gestionnaires
     */
    public function gestionnaireStats()
    {
        $stats = [
            'total' => Personnel::whereHas('user', function ($q) {
                $q->whereHas('roles', function ($roleQuery) {
                    $roleQuery->where('name', RoleEnum::GESTIONNAIRE->value);
                });
            })->count(),
            
            'actifs' => Personnel::whereHas('user', function ($q) {
                $q->whereHas('roles', function ($roleQuery) {
                    $roleQuery->where('name', RoleEnum::GESTIONNAIRE->value);
                })->where('est_actif', true);
            })->count(),
            
            'suspendus' => Personnel::whereHas('user', function ($q) {
                $q->whereHas('roles', function ($roleQuery) {
                    $roleQuery->where('name', RoleEnum::GESTIONNAIRE->value);
                })->where('est_actif', false);
            })->count(),
        ];

        return ApiResponse::success($stats, 'Statistiques des gestionnaires récupérées avec succès');
    }
}
