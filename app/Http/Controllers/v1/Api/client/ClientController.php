<?php

namespace App\Http\Controllers\v1\Api\client;

use App\Enums\TypeDemandeurEnum;
use App\Enums\StatutDemandeAdhesionEnum;
use App\Enums\StatutPropositionContratEnum;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
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
                        'type_contrat' => $proposition->contrat->type_contrat,
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

        // Récupération des paramètres d'entrée
        $params = $this->getRequestParams($request);
    
        // Construction de la requête de base
        $query = ClientContrat::with(['contrat.categoriesGaranties.garanties'])
            ->where('user_id', $user->id)
            ->when($params['statut'], fn($q) => $q->where('statut', $params['statut']))
            ->when($params['dateDebut'], fn($q) => $q->whereDate('date_debut', '>=', $params['dateDebut']))
            ->when($params['dateFin'], fn($q) => $q->whereDate('date_fin', '<=', $params['dateFin']))
            ->orderBy($params['sortBy'], $params['sortOrder']);
    
        // Pagination
        $contrats = $query->paginate($params['perPage']);
        
      
    
        return ApiResponse::success($contrats, 'Contrats récupérés avec succès');
    
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

private function getRequestParams(Request $request): array
{
    return [
        'statut' => $request->input('statut'),
        'dateDebut' => $request->input('date_debut'),
        'dateFin' => $request->input('date_fin'),
        'sortBy' => $request->input('sort_by', 'created_at'),
        'sortOrder' => $request->input('sort_order', 'desc'),
        'perPage' => $request->input('per_page', 15),
    ];
}

private function getPaginationData($contrats): array
{
    return [
        'current_page' => $contrats->currentPage(),
        'per_page' => $contrats->perPage(),
        'total' => $contrats->total(),
        'last_page' => $contrats->lastPage(),
        'from' => $contrats->firstItem(),
        'to' => $contrats->lastItem(),
        'has_more_pages' => $contrats->hasMorePages(),
    ];
}

    



     /**
     * Refuser une proposition de contrat
     */
    public function refuserContrat(Request $request, int $propositionId)
    {
        $validatedData = Validator::make($request->all(), [
            'raison_refus' => 'required|string|max:1000',
        ]);

        if($validatedData->fails()) {
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

            if(!$proposition) {
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
                    'contrat_type' => $proposition->contrat->type_contrat,
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

            // Vérifier que l'utilisateur est un client physique
            if ($user->entreprise) {
                return ApiResponse::error('Cette fonctionnalité est réservée aux clients physiques', 403);
            }

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