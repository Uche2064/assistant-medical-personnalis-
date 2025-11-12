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
                'adresse' => $validated['adresse'] ?? null,
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
                'gestionnaire_id' => Auth::user()->personnel->id,
            ];

            // Générer un code de parrainage pour les commerciaux
            if ($validated['role'] === RoleEnum::COMMERCIAL->value) {
                $userData['code_parrainage'] = Personnel::genererCodeParainage();
            }

            $personnel = Personnel::create($personnelData);

            // Envoyer les identifiants par email
            dispatch(new SendCredentialsJob($user, $password));

            Log::info("Personnel créé - Email: {$user->email}, Mot de passe: {$password}");

            DB::commit();

            return ApiResponse::success([
                'personnel' => new UserResource($user->load(['roles', 'personnel', 'personne']))
            ], RoleEnum::getLabel($validated['role']).' créé avec succès. Les identifiants ont été envoyés par email.', 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création du personnel: ' . $e->getMessage());
            return ApiResponse::error('Erreur lors de la création du personnel', 500, $e->getMessage());
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
        Log::info("Suspendre/désactiver un personnel - ID: {$id}");
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
     * Statistiques complètes des personnels gérés par le gestionnaire
     */
    public function personnelStats()
    {
        try {
            $currentGestionnaire = Auth::user();

            // Vue d'ensemble
            $vueEnsemble = $this->getVueEnsemblePersonnels($currentGestionnaire);

            // Répartitions
            $repartitions = $this->getRepartitionsPersonnels($currentGestionnaire);

            // Évolution mensuelle
            $evolutionMensuelle = $this->getEvolutionMensuellePersonnels($currentGestionnaire);

            // Derniers personnels créés
            $derniersPersonnels = $this->getDerniersPersonnels($currentGestionnaire);

            // Top 5 par rôle
            $topParRole = $this->getTopPersonnelsParRole($currentGestionnaire);

            return ApiResponse::success([
                'vue_ensemble' => $vueEnsemble,
                'repartitions' => $repartitions,
                'evolution_mensuelle' => $evolutionMensuelle,
                'derniers_personnels' => $derniersPersonnels,
                'top_par_role' => $topParRole
            ], 'Statistiques des personnels récupérées avec succès');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des statistiques: ' . $e->getMessage());
            return ApiResponse::error('Erreur lors de la récupération des statistiques', 500, $e->getMessage());
        }
    }

    /**
     * Vue d'ensemble des statistiques clés
     */
    private function getVueEnsemblePersonnels($currentGestionnaire)
    {
        $baseQuery = Personnel::where('id', '!=', $currentGestionnaire->personnel->id)
            ->whereNotNull('gestionnaire_id');

        $total = $baseQuery->clone()->count();
        $actifs = $baseQuery->clone()->whereHas('user', function ($q) {
            $q->where('est_actif', true);
        })->count();
        $inactifs = $baseQuery->clone()->whereHas('user', function ($q) {
            $q->where('est_actif', false);
        })->count();

        return [
            'total' => $total,
            'actifs' => $actifs,
            'inactifs' => $inactifs,
            'taux_activation' => $total > 0 ? round(($actifs / $total) * 100, 2) : 0,
            'nouveaux_ce_mois' => $baseQuery->clone()
                ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->count()
        ];
    }

    /**
     * Répartitions par rôle et sexe
     */
    private function getRepartitionsPersonnels($currentGestionnaire)
    {
        $personnels = Personnel::with(['user.roles', 'user.personne'])
            ->where('id', '!=', $currentGestionnaire->personnel->id)
            ->whereNotNull('gestionnaire_id')
            ->whereHas('user.roles')
            ->get();

        $total = $personnels->count();

        // Répartition par rôle
        $parRole = $personnels->groupBy(function ($personnel) {
            return $personnel->user->roles->first()->name ?? 'Aucun rôle';
        })->map(function ($group) use ($total) {
            $count = $group->count();
            return [
                'count' => $count,
                'pourcentage' => $total > 0 ? round(($count / $total) * 100, 2) : 0,
                'actifs' => $group->filter(function ($p) {
                    return $p->user->est_actif;
                })->count(),
                'inactifs' => $group->filter(function ($p) {
                    return !$p->user->est_actif;
                })->count()
            ];
        });

        // Répartition par sexe
        $parSexe = $personnels->groupBy(function ($personnel) {
            return optional($personnel->user->personne)->sexe ?? 'Non spécifié';
        })->map(function ($group) use ($total) {
            $count = $group->count();
            return [
                'count' => $count,
                'pourcentage' => $total > 0 ? round(($count / $total) * 100, 2) : 0
            ];
        });

        return [
            'par_role' => $parRole,
            'par_sexe' => $parSexe
        ];
    }

    /**
     * Évolution mensuelle des créations (12 derniers mois)
     */
    private function getEvolutionMensuellePersonnels($currentGestionnaire)
    {
        $evolution = [];
        $maintenant = now();

        for ($i = 11; $i >= 0; $i--) {
            $date = $maintenant->copy()->subMonths($i);
            $moisDebut = $date->copy()->startOfMonth();
            $moisFin = $date->copy()->endOfMonth();

            $personnelsCeMois = Personnel::where('id', '!=', $currentGestionnaire->personnel->id)
                ->whereNotNull('gestionnaire_id')
                ->whereBetween('created_at', [$moisDebut, $moisFin])
                ->with(['user.roles'])
                ->get();

            $total = $personnelsCeMois->count();
            $actifs = $personnelsCeMois->filter(function ($p) {
                return $p->user->est_actif;
            })->count();

            // Répartition par rôle pour ce mois
            $parRole = $personnelsCeMois->groupBy(function ($personnel) {
                return $personnel->user->roles->first()->name ?? 'Aucun rôle';
            })->map(function ($group) {
                return $group->count();
            });

            $evolution[] = [
                'mois' => $date->format('Y-m'),
                'mois_nom' => $date->format('M Y'),
                'mois_complet' => $date->format('F Y'),
                'total' => $total,
                'actifs' => $actifs,
                'inactifs' => $total - $actifs,
                'par_role' => $parRole
            ];
        }

        return $evolution;
    }

    /**
     * Derniers personnels créés (10 derniers)
     */
    private function getDerniersPersonnels($currentGestionnaire)
    {
        return Personnel::with(['user.roles', 'user.personne'])
            ->where('id', '!=', $currentGestionnaire->personnel->id)
            ->whereNotNull('gestionnaire_id')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($personnel) {
                return [
                    'id' => $personnel->id,
                    'nom_complet' => $personnel->nom . ' ' . ($personnel->prenoms ?? ''),
                    'email' => $personnel->user->email,
                    'role' => $personnel->user->roles->first()->name ?? 'Aucun rôle',
                    'role_label' => RoleEnum::getLabel($personnel->user->roles->first()->name ?? ''),
                    'sexe' => optional($personnel->user->personne)->sexe,
                    'est_actif' => $personnel->user->est_actif,
                    'date_creation' => $personnel->created_at->format('Y-m-d H:i:s'),
                    'date_creation_formatee' => $personnel->created_at->format('d/m/Y à H:i'),
                    'anciennete_jours' => now()->diffInDays($personnel->created_at)
                ];
            });
    }

    /**
     * Top 5 personnels par rôle (les plus anciens et actifs)
     */
    private function getTopPersonnelsParRole($currentGestionnaire)
    {
        $roles = [
            RoleEnum::COMMERCIAL->value,
            RoleEnum::TECHNICIEN->value,
            RoleEnum::MEDECIN_CONTROLEUR->value,
            RoleEnum::COMPTABLE->value
        ];

        $topParRole = [];

        foreach ($roles as $role) {
            $personnels = Personnel::with(['user.roles', 'user.personne'])
                ->where('id', '!=', $currentGestionnaire->personnel->id)
                ->whereNotNull('gestionnaire_id')
                ->whereHas('user', function ($q) {
                    $q->where('est_actif', true);
                })
                ->whereHas('user.roles', function ($q) use ($role) {
                    $q->where('name', $role);
                })
                ->orderBy('created_at', 'asc')
                ->limit(5)
                ->get()
                ->map(function ($personnel, $index) {
                    return [
                        'position' => $index + 1,
                        'id' => $personnel->id,
                        'nom_complet' => $personnel->nom . ' ' . ($personnel->prenoms ?? ''),
                        'email' => $personnel->user->email,
                        'sexe' => optional($personnel->user->personne)->sexe,
                        'date_creation' => $personnel->created_at->format('Y-m-d H:i:s'),
                        'date_creation_formatee' => $personnel->created_at->format('d/m/Y à H:i'),
                        'anciennete_jours' => now()->diffInDays($personnel->created_at),
                        'anciennete_formatee' => now()->diffForHumans($personnel->created_at)
                    ];
                });

            if ($personnels->isNotEmpty()) {
                $topParRole[$role] = [
                    'role_label' => RoleEnum::getLabel($role),
                    'personnels' => $personnels
                ];
            }
        }

        return $topParRole;
    }
}
