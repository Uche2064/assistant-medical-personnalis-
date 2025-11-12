<?php

namespace App\Http\Controllers\v1\Api\admin;

use App\Enums\ClientTypeEnum;
use App\Enums\RoleEnum;
use App\Helpers\ApiResponse;
use App\Helpers\ImageUploadHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\admin\StoreGestionnaireRequest;
use App\Http\Resources\UserResource;
use App\Jobs\SendCredentialsJob;
use App\Models\Client;
use App\Models\CommercialParrainageCode;
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

    /**
     * Dashboard global de l'admin
     */
    public function dashboardGlobal()
    {
        try {
            // 1. Vue d'ensemble des statistiques clés
            $vueEnsemble = $this->getVueEnsemble();
            
            // 2. Graphiques et analyses
            $graphiques = $this->getGraphiquesAnalyses();
            
            // 3. Activités récentes
            $activitesRecentes = $this->getActivitesRecentes();
            
            // 4. Top 5 commerciaux
            $topCommerciaux = $this->getTopCommerciaux();
            
            // 5. Top 5 gestionnaires
            $topGestionnaires = $this->getTopGestionnaires();
            
            // 6. Top 5 clients
            $topClients = $this->getTopClients();

            return ApiResponse::success([
                'vue_ensemble' => $vueEnsemble,
                'graphiques' => $graphiques,
                'activites_recentes' => $activitesRecentes,
                'top_commerciaux' => $topCommerciaux,
                'top_gestionnaires' => $topGestionnaires,
                'top_clients' => $topClients
            ], 'Dashboard global récupéré avec succès');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération du dashboard global: ' . $e->getMessage());
            return ApiResponse::error('Erreur lors de la récupération du dashboard global', 500, $e->getMessage());
        }
    }

    /**
     * Vue d'ensemble des statistiques clés
     */
    private function getVueEnsemble()
    {
        // Gestionnaires
        $gestionnairesQuery = User::whereHas('roles', function ($q) {
            $q->where('name', RoleEnum::GESTIONNAIRE->value);
        });
        
        $totalGestionnaires = $gestionnairesQuery->count();
        $gestionnairesActifs = $gestionnairesQuery->clone()->where('est_actif', true)->count();
        $gestionnairesInactifs = $gestionnairesQuery->clone()->where('est_actif', false)->count();

        // Commerciaux
        $commerciauxQuery = User::whereHas('roles', function ($q) {
            $q->where('name', RoleEnum::COMMERCIAL->value);
        });
        
        $totalCommerciaux = $commerciauxQuery->count();
        $commerciauxActifs = $commerciauxQuery->clone()->where('est_actif', true)->count();
        $commerciauxInactifs = $commerciauxQuery->clone()->where('est_actif', false)->count();

        // Clients
        $clientsQuery = User::whereHas('roles', function ($q) {
            $q->where('name', RoleEnum::CLIENT->value);
        });
        
        $totalClients = $clientsQuery->count();
        $clientsActifs = $clientsQuery->clone()->where('est_actif', true)->count();
        $clientsInactifs = $clientsQuery->clone()->where('est_actif', false)->count();

        // Codes de parrainage actifs
        $codesParrainageActifs = CommercialParrainageCode::where('est_actif', true)
            ->where('date_expiration', '>', now())
            ->count();

        return [
            'gestionnaires' => [
                'total' => $totalGestionnaires,
                'actifs' => $gestionnairesActifs,
                'inactifs' => $gestionnairesInactifs,
                'taux_activation' => $totalGestionnaires > 0 ? round(($gestionnairesActifs / $totalGestionnaires) * 100, 2) : 0
            ],
            'commerciaux' => [
                'total' => $totalCommerciaux,
                'actifs' => $commerciauxActifs,
                'inactifs' => $commerciauxInactifs,
                'taux_activation' => $totalCommerciaux > 0 ? round(($commerciauxActifs / $totalCommerciaux) * 100, 2) : 0,
                'codes_parrainage_actifs' => $codesParrainageActifs
            ],
            'clients' => [
                'total' => $totalClients,
                'actifs' => $clientsActifs,
                'inactifs' => $clientsInactifs,
                'taux_activation' => $totalClients > 0 ? round(($clientsActifs / $totalClients) * 100, 2) : 0
            ],
            'total_utilisateurs' => $totalGestionnaires + $totalCommerciaux + $totalClients,
            'total_utilisateurs_actifs' => $gestionnairesActifs + $commerciauxActifs + $clientsActifs
        ];
    }

    /**
     * Graphiques et analyses
     */
    private function getGraphiquesAnalyses()
    {
        // Évolution mensuelle (12 derniers mois)
        $evolutionMensuelle = $this->getEvolutionMensuelle();
        
        // Répartition par sexe des gestionnaires
        $repartitionSexeGestionnaires = $this->getRepartitionSexeGestionnaires();
        
        // Répartition des clients par type
        $repartitionClientsParType = $this->getRepartitionClientsParType();
        
        // Taux d'activation par rôle
        $tauxActivation = $this->getTauxActivationParRole();

        return [
            'evolution_mensuelle' => $evolutionMensuelle,
            'repartition_sexe_gestionnaires' => $repartitionSexeGestionnaires,
            'repartition_clients_par_type' => $repartitionClientsParType,
            'taux_activation_par_role' => $tauxActivation
        ];
    }

    /**
     * Évolution mensuelle des utilisateurs (12 derniers mois)
     */
    private function getEvolutionMensuelle()
    {
        $evolution = [];
        $maintenant = now();
        
        for ($i = 11; $i >= 0; $i--) {
            $date = $maintenant->copy()->subMonths($i);
            $moisDebut = $date->copy()->startOfMonth();
            $moisFin = $date->copy()->endOfMonth();
            
            // Gestionnaires
            $gestionnaires = User::whereHas('roles', function ($q) {
                $q->where('name', RoleEnum::GESTIONNAIRE->value);
            })->whereBetween('created_at', [$moisDebut, $moisFin])->count();
            
            // Commerciaux
            $commerciaux = User::whereHas('roles', function ($q) {
                $q->where('name', RoleEnum::COMMERCIAL->value);
            })->whereBetween('created_at', [$moisDebut, $moisFin])->count();
            
            // Clients
            $clients = User::whereHas('roles', function ($q) {
                $q->where('name', RoleEnum::CLIENT->value);
            })->whereBetween('created_at', [$moisDebut, $moisFin])->count();
            
            $evolution[] = [
                'mois' => $date->format('Y-m'),
                'mois_nom' => $date->format('M Y'),
                'mois_complet' => $date->format('F Y'),
                'gestionnaires' => $gestionnaires,
                'commerciaux' => $commerciaux,
                'clients' => $clients,
                'total' => $gestionnaires + $commerciaux + $clients
            ];
        }
        
        return $evolution;
    }

    /**
     * Répartition par sexe des gestionnaires
     */
    private function getRepartitionSexeGestionnaires()
    {
        $gestionnaires = Personnel::whereHas('user', function ($q) {
            $q->whereHas('roles', function ($roleQuery) {
                $roleQuery->where('name', RoleEnum::GESTIONNAIRE->value);
            });
        })
        ->with('user.personne')
        ->get();

        $repartition = $gestionnaires->groupBy(function ($personnel) {
            return optional($personnel->user->personne)->sexe ?? 'Non spécifié';
        })->map(function ($group) {
            return $group->count();
        });

        $total = $gestionnaires->count();

        return [
            'data' => $repartition,
            'pourcentages' => $repartition->map(function ($count) use ($total) {
                return $total > 0 ? round(($count / $total) * 100, 2) : 0;
            })
        ];
    }

    /**
     * Répartition des clients par type
     */
    private function getRepartitionClientsParType()
    {
        $clientsPhysiques = Client::where('type_client', ClientTypeEnum::PHYSIQUE->value)->count();
        $clientsMoraux = Client::where('type_client', ClientTypeEnum::MORAL->value)->count();
        $total = $clientsPhysiques + $clientsMoraux;

        return [
            'physiques' => $clientsPhysiques,
            'moraux' => $clientsMoraux,
            'total' => $total,
            'pourcentage_physiques' => $total > 0 ? round(($clientsPhysiques / $total) * 100, 2) : 0,
            'pourcentage_moraux' => $total > 0 ? round(($clientsMoraux / $total) * 100, 2) : 0
        ];
    }

    /**
     * Taux d'activation par rôle
     */
    private function getTauxActivationParRole()
    {
        $roles = [
            'gestionnaires' => RoleEnum::GESTIONNAIRE->value,
            'commerciaux' => RoleEnum::COMMERCIAL->value,
            'clients' => RoleEnum::CLIENT->value
        ];

        $taux = [];
        foreach ($roles as $key => $role) {
            $total = User::whereHas('roles', function ($q) use ($role) {
                $q->where('name', $role);
            })->count();
            
            $actifs = User::whereHas('roles', function ($q) use ($role) {
                $q->where('name', $role);
            })->where('est_actif', true)->count();
            
            $taux[$key] = [
                'total' => $total,
                'actifs' => $actifs,
                'inactifs' => $total - $actifs,
                'taux' => $total > 0 ? round(($actifs / $total) * 100, 2) : 0
            ];
        }

        return $taux;
    }

    /**
     * Activités récentes
     */
    private function getActivitesRecentes()
    {
        // Derniers gestionnaires créés (5 derniers)
        $derniersGestionnaires = User::whereHas('roles', function ($q) {
            $q->where('name', RoleEnum::GESTIONNAIRE->value);
        })
        ->with(['personne', 'personnel'])
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get()
        ->map(function ($user) {
            return [
                'id' => $user->id,
                'nom_complet' => optional($user->personne)->nom . ' ' . optional($user->personne)->prenoms,
                'email' => $user->email,
                'est_actif' => $user->est_actif,
                'date_creation' => $user->created_at->format('Y-m-d H:i:s'),
                'date_creation_formatee' => $user->created_at->format('d/m/Y à H:i')
            ];
        });

        // Derniers commerciaux créés (5 derniers)
        $derniersCommerciaux = User::whereHas('roles', function ($q) {
            $q->where('name', RoleEnum::COMMERCIAL->value);
        })
        ->with(['personne', 'personnel'])
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get()
        ->map(function ($user) {
            return [
                'id' => $user->id,
                'nom_complet' => optional($user->personne)->nom . ' ' . optional($user->personne)->prenoms,
                'email' => $user->email,
                'est_actif' => $user->est_actif,
                'date_creation' => $user->created_at->format('Y-m-d H:i:s'),
                'date_creation_formatee' => $user->created_at->format('d/m/Y à H:i')
            ];
        });

        // Derniers clients créés (5 derniers)
        $derniersClients = User::whereHas('roles', function ($q) {
            $q->where('name', RoleEnum::CLIENT->value);
        })
        ->with(['personne', 'client'])
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get()
        ->map(function ($user) {
            return [
                'id' => $user->id,
                'nom_complet' => optional($user->personne)->nom . ' ' . optional($user->personne)->prenoms,
                'email' => $user->email,
                'est_actif' => $user->est_actif,
                'type_client' => optional($user->client)->type_client,
                'date_creation' => $user->created_at->format('Y-m-d H:i:s'),
                'date_creation_formatee' => $user->created_at->format('d/m/Y à H:i')
            ];
        });

        return [
            'derniers_gestionnaires' => $derniersGestionnaires,
            'derniers_commerciaux' => $derniersCommerciaux,
            'derniers_clients' => $derniersClients
        ];
    }

    /**
     * Top 5 des commerciaux par performance
     */
    private function getTopCommerciaux()
    {
        $commerciaux = User::whereHas('roles', function ($q) {
            $q->where('name', RoleEnum::COMMERCIAL->value);
        })
        ->with(['personne', 'personnel'])
        ->withCount(['clientsParraines' => function ($query) {
            $query->whereHas('roles', function ($q) {
                $q->where('name', RoleEnum::CLIENT->value);
            });
        }])
        ->orderBy('clients_parraines_count', 'desc')
        ->limit(5)
        ->get()
        ->map(function ($commercial, $index) {
            $clientsActifs = $commercial->clientsParraines()
                ->whereHas('roles', function ($q) {
                    $q->where('name', RoleEnum::CLIENT->value);
                })
                ->where('est_actif', true)
                ->count();

            $codeParrainage = CommercialParrainageCode::getCurrentCode($commercial->id);

            return [
                'position' => $index + 1,
                'id' => $commercial->id,
                'nom_complet' => optional($commercial->personne)->nom . ' ' . optional($commercial->personne)->prenoms,
                'email' => $commercial->email,
                'total_clients' => $commercial->clients_parraines_count,
                'clients_actifs' => $clientsActifs,
                'clients_inactifs' => $commercial->clients_parraines_count - $clientsActifs,
                'taux_activation' => $commercial->clients_parraines_count > 0 
                    ? round(($clientsActifs / $commercial->clients_parraines_count) * 100, 2) 
                    : 0,
                'code_parrainage_actuel' => $codeParrainage ? $codeParrainage->code_parrainage : null,
                'date_expiration_code' => $codeParrainage ? $codeParrainage->date_expiration->format('Y-m-d') : null
            ];
        });

        return $commerciaux;
    }

    /**
     * Top 5 des gestionnaires (les plus anciens et actifs)
     */
    private function getTopGestionnaires()
    {
        $gestionnaires = User::whereHas('roles', function ($q) {
            $q->where('name', RoleEnum::GESTIONNAIRE->value);
        })
        ->with(['personne', 'personnel'])
        ->where('est_actif', true)
        ->orderBy('created_at', 'asc')
        ->limit(5)
        ->get()
        ->map(function ($gestionnaire, $index) {
            return [
                'position' => $index + 1,
                'id' => $gestionnaire->id,
                'nom_complet' => optional($gestionnaire->personne)->nom . ' ' . optional($gestionnaire->personne)->prenoms,
                'email' => $gestionnaire->email,
                'sexe' => optional($gestionnaire->personne)->sexe,
                'est_actif' => $gestionnaire->est_actif,
                'date_creation' => $gestionnaire->created_at->format('Y-m-d H:i:s'),
                'date_creation_formatee' => $gestionnaire->created_at->format('d/m/Y à H:i'),
                'anciennete_jours' => now()->diffInDays($gestionnaire->created_at),
                'anciennete_formatee' => now()->diffForHumans($gestionnaire->created_at)
            ];
        });

        return $gestionnaires;
    }

    /**
     * Top 5 des clients (par nombre de contrats ou ancienneté)
     */
    private function getTopClients()
    {
        // Récupérer les clients avec le nombre de contrats via une sous-requête
        $clients = User::whereHas('roles', function ($q) {
            $q->where('name', RoleEnum::CLIENT->value);
        })
        ->with(['personne', 'client'])
        ->where('est_actif', true)
        ->get()
        ->map(function ($user) {
            // Compter les contrats manuellement
            $nombreContrats = 0;
            if ($user->client) {
                $nombreContrats = DB::table('clients_contrats')
                    ->where('client_id', $user->client->id)
                    ->count();
            }
            
            $user->nombre_contrats = $nombreContrats;
            return $user;
        })
        ->sortByDesc('nombre_contrats')
        ->take(5)
        ->values()
        ->map(function ($client, $index) {
            $typeClient = optional($client->client)->type_client;
            $commercial = $client->commercial;
            
            return [
                'position' => $index + 1,
                'id' => $client->id,
                'nom_complet' => optional($client->personne)->nom . ' ' . optional($client->personne)->prenoms,
                'email' => $client->email,
                'type_client' => $typeClient,
                'est_actif' => $client->est_actif,
                'nombre_contrats' => $client->nombre_contrats,
                'commercial' => $commercial ? [
                    'id' => $commercial->id,
                    'nom_complet' => optional($commercial->personne)->nom . ' ' . optional($commercial->personne)->prenoms,
                    'email' => $commercial->email
                ] : null,
                'code_parrainage' => $client->code_parrainage,
                'date_creation' => $client->created_at->format('Y-m-d H:i:s'),
                'date_creation_formatee' => $client->created_at->format('d/m/Y à H:i'),
                'anciennete_jours' => now()->diffInDays($client->created_at)
            ];
        });

        return $clients;
    }

}
