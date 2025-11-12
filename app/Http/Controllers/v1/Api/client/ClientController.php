<?php

namespace App\Http\Controllers\v1\Api\client;

use App\Enums\TypeDemandeurEnum;
use App\Enums\StatutDemandeAdhesionEnum;
use App\Enums\StatutPropositionContratEnum;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\ClientContratResource;
use App\Models\Assure;
use App\Models\ClientContrat;
use App\Models\DemandeAdhesion;
use App\Models\PropositionContrat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    private $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }


    /**
     * Récupérer les contrats proposés pour un client
     */
    public function getContratsProposes()
    {
        try {
            $user = Auth::user();

            // Récupérer les propositions de contrats pour ce client avec toutes les relations nécessaires
            $propositions = PropositionContrat::with([
                'contrat',
                'contrat.categoriesGaranties.garanties'
            ])
            ->whereHas('demandeAdhesion', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->where('statut', StatutPropositionContratEnum::PROPOSEE->value)
            ->get();

            $contratsProposes = $propositions->map(function ($proposition) {
                // Structurer les catégories de garanties avec leurs garanties
                $categoriesGaranties = $proposition->contrat->categoriesGaranties->map(function ($categorie) {
                    return [
                        'id' => $categorie->id,
                        'libelle' => $categorie->libelle,
                        'description' => $categorie->description,
                        'couverture' => $categorie->pivot->couverture, // Couverture depuis la table pivot
                        'couverture_formatted' => number_format($categorie->pivot->couverture, 0, ',', ' ') . ' FCFA',
                        'garanties' => $categorie->garanties->map(function ($garantie) {
                            return [
                                'id' => $garantie->id,
                                'libelle' => $garantie->libelle,
                                'plafond' => $garantie->plafond,
                                'prix_standard' => $garantie->prix_standard,
                                'taux_couverture' => $garantie->taux_couverture,
                                'plafond_formatted' => number_format($garantie->plafond, 0, ',', ' ') . ' FCFA',
                                'prix_standard_formatted' => number_format($garantie->prix_standard, 0, ',', ' ') . ' FCFA',
                                'montant_couvert' => $garantie->getCoverageAmountAttribute(),
                                'montant_couvert_formatted' => number_format($garantie->getCoverageAmountAttribute(), 0, ',', ' ') . ' FCFA'
                            ];
                        })
                    ];
                });

                return [
                    'proposition_id' => $proposition->id,
                    'categories_garanties' => $categoriesGaranties,
                    'created_at' => $proposition->created_at,
                    'statut' => $proposition->statut?->value ?? $proposition->statut,
                    'contrat' => [
                        'id' => $proposition->contrat->id,
                        'libelle' => $proposition->contrat->libelle,
                    ],
                    'details_proposition' => [
                        'prime_proposee' => $proposition->getPrimeAttribute(),
                        'prime_proposee_formatted' => $proposition->getPrimeFormattedAttribute(),
                        'prime_totale' => $proposition->getPrimeTotaleAttribute(),
                        'prime_totale_formatted' => $proposition->getPrimeTotaleFormattedAttribute(),
                        'taux_couverture' => $proposition->getTauxCouvertureAttribute(),
                        'frais_gestion' => $proposition->getFraisGestionAttribute(),
                        'date_proposition' => $proposition->created_at,
                    ],
                    'technicien' => [
                        'nom' => $proposition->technicien->nom
                    ],
                ];
            });

            Log::info($contratsProposes);

            return ApiResponse::success($contratsProposes, 'Contrats proposés récupérés avec succès');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des contrats proposés', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);

            return ApiResponse::error('Erreur lors de la récupération des contrats proposés: ' . $e->getMessage(), 500);
        }
    }


    public function mesContrats(Request $request)
{
    try {
        $user = Auth::user();

        // Construction de la requête de base avec toutes les relations nécessaires
        $query = ClientContrat::with([
                'typeContrat.categoriesGaranties.garanties',
                'client.user.personne',
            'prestataires'
        ])
                ->whereHas('client', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->orderByDesc('created_at');

            // Récupération des contrats
            $contrats = $query->get();

        // Utiliser la resource pour formater les données
            $contratsFormatted = $contrats->map(function ($contrat) {
                return new ClientContratResource($contrat);
            });

            return ApiResponse::success($contratsFormatted, 'Contrats récupérés avec succès');
    } catch (\Exception $e) {
        Log::error('Erreur lors de la récupération des contrats utilisateur', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'user_id' => Auth::id(),
            'filters' => $request->all()
        ]);

        return ApiResponse::error('Erreur lors de la récupération des contrats: ' . $e->getMessage(), 500);
    }
}

    /**
     * Récupérer les détails d'un contrat spécifique
     */
    public function contratDetails(Request $request, int $id)
    {
        try {
            $user = Auth::user();

            // Récupérer le contrat avec toutes les relations nécessaires
            $contrat = ClientContrat::with([
                'typeContrat.categoriesGaranties.garanties',
                'client.user.personne',
                'prestataires.prestataire'
            ])
            ->whereHas('client', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->find($id);

            if (!$contrat) {
                return ApiResponse::error('Contrat non trouvé ou vous n\'êtes pas autorisé à le consulter', 404);
            }

            // Utiliser la resource pour formater les données
            $contratFormatted = new ClientContratResource($contrat);

            return ApiResponse::success($contratFormatted, 'Détails du contrat récupérés avec succès');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des détails du contrat', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'contrat_id' => $id
            ]);

            return ApiResponse::error('Erreur lors de la récupération des détails du contrat: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Récupérer les statistiques du client selon son type
     */
    public function statistiques(Request $request)
    {
        try {
            $user = Auth::user();
            $client = $user->client;

            if (!$client) {
                return ApiResponse::error('Profil client non trouvé', 404);
            }

            // Vérifier le type de client et retourner les statistiques appropriées
            if ($client->type_client === \App\Enums\ClientTypeEnum::PHYSIQUE) {
                return $this->getStatistiquesClientPhysique($client, $user);
            } elseif ($client->type_client === \App\Enums\ClientTypeEnum::MORAL) {
                return $this->getStatistiquesClientMoral($client, $user);
            } else {
                return ApiResponse::error('Type de client non reconnu', 400);
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des statistiques client', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);

            return ApiResponse::error('Erreur lors de la récupération des statistiques: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Statistiques pour les clients physiques (particuliers)
     */
    private function getStatistiquesClientPhysique($client, $user)
    {
        // Récupérer l'assuré principal
        $assurePrincipal = $user->assure;
        if (!$assurePrincipal) {
            return ApiResponse::error('Profil assuré non trouvé', 404);
        }

        // Statistiques des bénéficiaires
        $totalBeneficiaires = $assurePrincipal->beneficiaires()->count();
        $beneficiaires = $assurePrincipal->beneficiaires()->with('user.personne')->get();

        // Statistiques des contrats actifs
        $contratsActifs = ClientContrat::with([
            'typeContrat.categoriesGaranties.garanties',
            'prestataires.prestataire'
        ])
        ->where('client_id', $client->id)
        ->where('statut', 'actif')
        ->get();

        // Statistiques des prestataires assignés
        $prestatairesAssignes = collect();
        $repartitionPrestataires = [];
        $totalPrestataires = 0;

        foreach ($contratsActifs as $contrat) {
            foreach ($contrat->prestataires as $clientPrestataire) {
                $prestataire = $clientPrestataire->prestataire;
                if ($prestataire) {
                    $prestatairesAssignes->push($prestataire);
                    $type = $prestataire->type_prestataire->value;
                    $repartitionPrestataires[$type] = ($repartitionPrestataires[$type] ?? 0) + 1;
                    $totalPrestataires++;
                }
            }
        }

        // Statistiques des sinistres
        $sinistres = \App\Models\Sinistre::whereHas('assure', function ($query) use ($assurePrincipal) {
            $query->where('assure_principal_id', $assurePrincipal->id)
                  ->orWhere('id', $assurePrincipal->id);
        })->with(['prestataire', 'factures'])->get();

        $statistiquesSinistres = [
            'total' => $sinistres->count(),
            'en_cours' => $sinistres->where('statut', 'en_cours')->count(),
            'clotures' => $sinistres->where('statut', 'cloture')->count(),
            'montant_total_reclame' => $sinistres->sum(function ($sinistre) {
                return $sinistre->factures->sum('montant_facture');
            }),
            'montant_total_rembourse' => $sinistres->sum(function ($sinistre) {
                return $sinistre->factures->where('statut', 'paye')->sum('montant_facture');
            })
        ];

        // Statistiques des garanties
        $categoriesGaranties = collect();
        $garantiesDetails = collect();

        foreach ($contratsActifs as $contrat) {
            if ($contrat->typeContrat && $contrat->typeContrat->categoriesGaranties) {
                foreach ($contrat->typeContrat->categoriesGaranties as $categorie) {
                    $categoriesGaranties->push([
                        'id' => $categorie->id,
                        'libelle' => $categorie->libelle,
                        'description' => $categorie->description,
                        'couverture' => $categorie->pivot->couverture ?? null
                    ]);

                    if ($categorie->garanties) {
                        foreach ($categorie->garanties as $garantie) {
                            $garantiesDetails->push([
                                'id' => $garantie->id,
                                'libelle' => $garantie->libelle,
                                'plafond' => $garantie->plafond,
                                'prix_standard' => $garantie->prix_standard,
                                'taux_couverture' => $garantie->taux_couverture,
                                'categorie_id' => $categorie->id,
                                'categorie_libelle' => $categorie->libelle
                            ]);
                        }
                    }
                }
            }
        }

        // Statistiques financières
        $soldeActuel = $user->solde ?? 0;
        $totalPrimes = $contratsActifs->sum(function ($contrat) {
            return $contrat->typeContrat->prime_standard ?? 0;
        });

        // Statistiques des remboursements (derniers 12 mois)
        $dateDebut = now()->subMonths(12);
        $remboursementsRecents = $sinistres->filter(function ($sinistre) use ($dateDebut) {
            return $sinistre->created_at >= $dateDebut;
        });

        $statistiquesRemboursements = [
            'total_derniers_12_mois' => $remboursementsRecents->count(),
            'montant_total_derniers_12_mois' => $remboursementsRecents->sum(function ($sinistre) {
                return $sinistre->factures->sum('montant_facture');
            }),
            'moyenne_mensuelle' => $remboursementsRecents->count() / 12
        ];

        // Statistiques des contrats
        $statistiquesContrats = [
            'total_actifs' => $contratsActifs->count(),
            'total_historique' => ClientContrat::where('client_id', $client->id)->count(),
            'contrats_par_statut' => ClientContrat::where('client_id', $client->id)
                ->groupBy('statut')
                ->selectRaw('statut, count(*) as total')
                ->pluck('total', 'statut')
        ];

        return ApiResponse::success([
            'type_client' => 'physique',
            'client_info' => [
                'id' => $client->id,
                'type_client' => $client->type_client->value,
                'type_client_label' => $client->type_client->getLabel(),
                'solde_actuel' => $soldeActuel,
                'solde_actuel_formatted' => number_format($soldeActuel, 0, ',', ' ') . ' FCFA',
                'total_primes' => $totalPrimes,
                'total_primes_formatted' => number_format($totalPrimes, 0, ',', ' ') . ' FCFA'
            ],

            'beneficiaires' => [
                'total' => $totalBeneficiaires,
                'details' => $beneficiaires->map(function ($beneficiaire) {
                    return [
                        'id' => $beneficiaire->id,
                        'nom' => $beneficiaire->user->personne->nom ?? null,
                        'prenoms' => $beneficiaire->user->personne->prenoms ?? null,
                        'nom_complet' => ($beneficiaire->user->personne->nom ?? '') . ' ' . ($beneficiaire->user->personne->prenoms ?? ''),
                        'lien_parente' => $beneficiaire->lien_parente,
                        'date_naissance' => $beneficiaire->user->personne->date_naissance ?? null,
                        'sexe' => $beneficiaire->user->personne->sexe ?? null
                    ];
                })
            ],

            'prestataires' => [
                'total_assignes' => $totalPrestataires,
                'repartition_par_type' => $repartitionPrestataires,
                'repartition_formatted' => collect($repartitionPrestataires)->map(function ($count, $type) use ($totalPrestataires) {
                    return [
                        'type' => $type,
                        'type_label' => \App\Enums\TypePrestataireEnum::from($type)->getLabel(),
                        'count' => $count,
                        'percentage' => $totalPrestataires > 0 ? round(($count / $totalPrestataires) * 100, 2) : 0
                    ];
                })->values(),
                'details' => $prestatairesAssignes->unique('id')->map(function ($prestataire) {
    return [
                        'id' => $prestataire->id,
                        'raison_sociale' => $prestataire->user->personne->nom ?? 'N/A',
                        'type_prestataire' => $prestataire->type_prestataire->value,
                        'type_prestataire_label' => $prestataire->type_prestataire->getLabel(),
                        'statut' => $prestataire->statut->value,
                        'statut_label' => $prestataire->statut->getLabel(),
                        'contact' => $prestataire->user->contact ?? null,
                        'adresse' => $prestataire->user->adresse ?? null
                    ];
                })
            ],

            'garanties' => [
                'categories' => $categoriesGaranties->unique('id')->values(),
                'garanties_details' => $garantiesDetails->unique('id')->values(),
                'total_categories' => $categoriesGaranties->unique('id')->count(),
                'total_garanties' => $garantiesDetails->unique('id')->count()
            ],

            'sinistres' => $statistiquesSinistres,

            'remboursements' => $statistiquesRemboursements,

            'contrats' => $statistiquesContrats,

            'resume' => [
                'total_beneficiaires' => $totalBeneficiaires + 1, // +1 pour l'assuré principal
                'total_prestataires' => $totalPrestataires,
                'total_contrats_actifs' => $contratsActifs->count(),
                'total_sinistres' => $statistiquesSinistres['total'],
                'solde_actuel' => $soldeActuel,
                'solde_actuel_formatted' => number_format($soldeActuel, 0, ',', ' ') . ' FCFA'
            ]
        ], 'Statistiques du client particulier récupérées avec succès');
    }

    /**
     * Statistiques pour les clients moraux (entreprises)
     */
    private function getStatistiquesClientMoral($client, $user)
    {
        // Pour l'instant, retourner un message indiquant que cette fonctionnalité sera implémentée
        return ApiResponse::success([
            'type_client' => 'moral',
            'message' => 'Statistiques pour les clients entreprise seront implémentées prochainement',
            'client_info' => [
                'id' => $client->id,
                'type_client' => $client->type_client->value,
                'type_client_label' => $client->type_client->getLabel()
            ]
        ], 'Statistiques du client entreprise récupérées avec succès');
    }

     /**
     * Refuser une proposition de contrat
     */
    public function refuserContrat(Request $request, int $propositionId)
    {
        $validatedData = Validator::make($request->all(), [
            'raison_refus' => 'required|string|max:1000',
        ]);

        if ($validatedData->fails()) {
            return ApiResponse::error('Erreur de validation: ' . $validatedData->errors()->first(), 422);
        }

        try {
            $user = Auth::user();

            // Récupérer la proposition
            $proposition = PropositionContrat::with([
                'demandeAdhesion.user.assure',
                'contrat',
                'technicien'
            ])->find($propositionId);

            if (!$proposition) {
                return ApiResponse::error('Proposition de contrat non trouvée', 404);
            }

            // Vérifier que la proposition appartient à l'utilisateur connecté
            if ($proposition->demandeAdhesion->user_id !== $user->id) {
                return ApiResponse::error('Accès non autorisé', 403);
            }

            // Vérifier que la proposition est en statut PROPOSEE
            if ($proposition->statut !== StatutPropositionContratEnum::PROPOSEE->value) {
                return ApiResponse::error('Cette proposition a déjà été traitée', 400);
            }

            DB::beginTransaction();

            try {
                // 1. Mettre à jour la proposition
                $proposition->update([
                    'statut' => StatutPropositionContratEnum::REFUSEE->value,
                    'raison_refus' => $request->raison_refus,
                    'date_refus' => now()
                ]);

                // 2. Mettre à jour la demande d'adhésion (revenir en attente)
                $proposition->demandeAdhesion->update([
                    'statut' => StatutDemandeAdhesionEnum::EN_ATTENTE->value
                ]);

                            // 3. Notification au technicien
            $this->notificationService->createNotification(
                $proposition->technicien->user_id,
                'Proposition de contrat refusée',
                "Le client {$proposition->demandeAdhesion->user->assure->nom} a refusé votre proposition de contrat.",
                'contrat_refuse_technicien',
                [
                    'client_nom' => $proposition->demandeAdhesion->user->nom,
                    'contrat_type' => $proposition->contrat->libelle,
                    'prime' => $proposition->prime_proposee,
                    'type' => 'contrat_refuse_technicien'
                ]
            );

                DB::commit();

                return ApiResponse::success([
                    'proposition_id' => $proposition->id,
                    'message' => 'Proposition refusée avec succès'
                ], 'Proposition refusée avec succès');
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors du refus du contrat', [
                'error' => $e->getMessage(),
                'proposition_id' => $propositionId,
                'user_id' => Auth::id()
            ]);

            return ApiResponse::error('Erreur lors du refus du contrat: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Récupérer les statistiques des bénéficiaires pour un client physique
     */
    public function stats()
    {
        try {
            $user = Auth::user();

            // // Vérifier que l'utilisateur est un client physique
            // if ($user->entreprise) {
            //     return ApiResponse::error('Cette fonctionnalité est réservée aux clients physiques', 403);
            // }

            // Récupérer l'assuré principal (est_principal = true, assure_principal_id = null)
            $assurePrincipal = Assure::where('user_id', $user->id)
                ->where('est_principal', true)
                ->whereNull('assure_principal_id')
                ->first();

            if (!$assurePrincipal) {
                return ApiResponse::error('Assuré principal non trouvé', 404);
            }

            // Récupérer tous les bénéficiaires (assure_principal_id = ID de l'assuré principal)
            $beneficiaires = Assure::where('assure_principal_id', $assurePrincipal->id)
                ->where('est_principal', false)
                ->get();

            // Combiner l'assuré principal et les bénéficiaires pour les statistiques
            $tousLesBeneficiaires = collect([$assurePrincipal])->merge($beneficiaires);

            // Statistiques générales
            $totalBeneficiaires = $tousLesBeneficiaires->count();
            $nombreBeneficiairesSecondaires = $beneficiaires->count();

            // Répartition par sexe
            $repartitionSexe = $tousLesBeneficiaires->groupBy('sexe')
                ->map(function ($group) use ($totalBeneficiaires) {
                    return [
                        'nombre' => $group->count(),
                        'pourcentage' => round(($group->count() / $totalBeneficiaires) * 100, 2)
                    ];
                });

            // Répartition par âge
            $repartitionAge = $tousLesBeneficiaires->groupBy(function ($beneficiaire) {
                $age = \Carbon\Carbon::parse($beneficiaire->date_naissance)->age;
                if ($age < 18) return 'Enfant (0-17 ans)';
                elseif ($age < 25) return 'Jeune adulte (18-24 ans)';
                elseif ($age < 50) return 'Adulte (25-49 ans)';
                elseif ($age < 65) return 'Senior (50-64 ans)';
                else return 'Aîné (65+ ans)';
            })->map(function ($group) use ($totalBeneficiaires) {
                return [
                    'nombre' => $group->count(),
                    'pourcentage' => round(($group->count() / $totalBeneficiaires) * 100, 2)
                ];
            });


            // Détails des bénéficiaires

            $stats = [
                'resume' => [
                    'total_beneficiaires' => $totalBeneficiaires,
                    'assure_principal' => [
                        'nom' => $assurePrincipal->nom . ' ' . $assurePrincipal->prenoms,
                        'age' => \Carbon\Carbon::parse($assurePrincipal->date_naissance)->age,
                        'sexe' => $assurePrincipal->sexe,
                        'profession' => $assurePrincipal->profession,
                    ],
                    'nombre_beneficiaires_secondaires' => $nombreBeneficiairesSecondaires,
                ],
                'repartition_sexe' => [
                    'hommes' => $repartitionSexe->get('M', ['nombre' => 0, 'pourcentage' => 0]),
                    'femmes' => $repartitionSexe->get('F', ['nombre' => 0, 'pourcentage' => 0]),
                ],
                'repartition_age' => $repartitionAge,
            ];

            return ApiResponse::success($stats, 'Statistiques des bénéficiaires récupérées avec succès');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des statistiques des bénéficiaires', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return ApiResponse::error('Erreur lors de la récupération des statistiques: ' . $e->getMessage(), 500);
        }
    }
}
