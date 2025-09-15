<?php

namespace App\Http\Controllers\v1\Api\admin;

use App\Enums\RoleEnum;
use App\Helpers\ApiResponse;
use App\Helpers\ImageUploadHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\admin\StoreGestionnaireRequest;
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

class AdminController extends Controller
{

    /**
     * Lister tous les gestionnaires avec filtres
     */
    public function indexGestionnaires()
    {
        $adminId = Auth::user()?->personnel?->id;

        $gestionnaires = Personnel::with('user.roles')
            ->whereHas(
                'user.roles',
                fn($q) =>
                $q->where('name', RoleEnum::GESTIONNAIRE->value)
            )
            ->when($adminId, fn($q) => $q->where('id', '!=', $adminId))
            ->get()
            ->pluck('user');

        return ApiResponse::success(
            UserResource::collection($gestionnaires),
            'Liste des gestionnaires récupérée avec succès'
        );
    }


    /**
     * Créer un nouveau gestionnaire
     */
    public function storeGestionnaire(StoreGestionnaireRequest $request)
    {
        $validated = $request->validated();
        $password = User::genererMotDePasse();
        $photoUrl = null;

        // Gestion de l'upload de la photo
        if (isset($validated['photo'])) {
            $photoUrl = ImageUploadHelper::uploadImage($validated['photo'], 'uploads/users/gestionnaires/' . $validated['email'] . '/');
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
                'contact' => $validated['contact'],
                'adresse' => $validated['adresse'] ?? null,
                'password' => Hash::make($password),
                'est_actif' => false,
                'mot_de_passe_a_changer' => true,
                'email_verifier_a' => now(),
                'photo_url' => $photoUrl,
                'personne_id' => $personne->id,
            ]);

            Log::info("adresse:".$validated['adresse']);

            // Assigner le rôle gestionnaire
            $user->assignRole(RoleEnum::GESTIONNAIRE->value);

            // Créer le personnel gestionnaire
            Personnel::create([
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
    public function toggleGestionnaireStatus($id)
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

        if ($gestionnaire->user->mot_de_passe_a_changer) {
            return ApiResponse::error('Le gestionnaire doit changer son mot de passe avant de pouvoir être réactivé', 400, null);
        }

        $gestionnaire->user->update(['est_actif' => !$gestionnaire->user->est_actif]);

        Log::info("Gestionnaire suspendu - ID: {$gestionnaire->id}, Email: {$gestionnaire->user->email}");

        return ApiResponse::success(null, 'Gestionnaire ' . ($gestionnaire->user->est_actif ? 'suspendu' : 'réactivé') . ' avec succès');
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

            'inactifs' => Personnel::whereHas('user', function ($q) {
                $q->whereHas('roles', function ($roleQuery) {
                    $roleQuery->where('name', RoleEnum::GESTIONNAIRE->value);
                })->where('est_actif', false);
            })->count(),

            'repartition_par_sexe' => Personnel::whereHas('user', function ($q) {
                $q->whereHas('roles', function ($roleQuery) {
                    $roleQuery->where('name', RoleEnum::GESTIONNAIRE->value);
                });
            })
            ->with('user.personne')
            ->get()
            ->groupBy(function ($personnel) {
                return optional($personnel->user->personne)->sexe ?? 'Non spécifié';
            })
            ->map(function ($group) {
                return $group->count();
            }),
        ];

        return ApiResponse::success($stats, 'Statistiques des gestionnaires récupérées avec succès');
    }
}
