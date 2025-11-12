<?php

namespace App\Http\Controllers\v1\Api\prestataire;

use App\Enums\StatutDemandeAdhesionEnum;
use App\Enums\StatutPrestataireEnum;
use App\Enums\EmailType;
use App\Enums\TypeDemandeurEnum;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\demande_adhesion\StoreDemandeAdhesionRequest;
use App\Http\Resources\AssureResource;
use App\Jobs\SendEmailJob;
use App\Models\Assure;
use App\Models\ClientContrat;
use App\Models\ClientPrestataire;
use App\Models\TypeContrat;
use App\Models\DemandeAdhesion;
use App\Models\Prestataire;
use App\Models\Sinistre;
use App\Models\Facture;
use App\Models\User;
use App\Services\DemandeAdhesionService;
use App\Services\DemandeValidatorService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PrestataireController extends Controller
{
    protected NotificationService $notificationService;
    protected DemandeValidatorService $demandeValidatorService;
    protected DemandeAdhesionService $demandeAdhesionService;

    public function __construct(
        NotificationService $notificationService,
        DemandeValidatorService $demandeValidatorService,
        DemandeAdhesionService $demandeAdhesionService
    ) {
        $this->notificationService = $notificationService;
        $this->demandeAdhesionService = $demandeAdhesionService;
    }


    /**
     * Dashboard statistiques pour le prestataire connecté
     */
    public function dashboard(Request $request)
    {
        try {
            $user = Auth::user()->load('prestataire');
            $prestataire = $user->prestataire;

            if (!$prestataire) {
                return ApiResponse::error('Prestataire non trouvé', 404);
            }

            // Assurés assignés (même logique que getAssure mais en count)
            $clientContratsAssignes = ClientPrestataire::where('prestataire_id', $prestataire->id)
                ->where('statut', 'actif')
                ->with(['clientContrat' => function ($query) {
                    $query->where('statut', 'actif')
                        ->where('date_debut', '<=', now())
                        ->where('date_fin', '>=', now());
                }])
                ->get()
                ->pluck('clientContrat')
                ->filter()
                ->pluck('id')
                ->toArray();

            $clientContrats = ClientContrat::whereIn('id', $clientContratsAssignes)->get();
            $clientIds = $clientContrats->pluck('user_id')->toArray();

            $assuresCount = Assure::where(function ($q) use ($clientIds) {
                $q->where(function ($subQ) use ($clientIds) {
                    $subQ->whereIn('user_id', $clientIds)
                        ->where('est_principal', true)
                        ->whereHas('user.demandesAdhesions', function ($demandeQ) {
                            $demandeQ->whereIn('statut', ['validee', 'acceptee']);
                        });
                })
                    ->orWhereHas('assurePrincipal', function ($principalQ) use ($clientIds) {
                        $principalQ->whereIn('user_id', $clientIds)
                            ->where('est_principal', true)
                            ->whereHas('user.demandesAdhesions', function ($demandeQ) {
                                $demandeQ->whereIn('statut', ['validee', 'acceptee']);
                            });
                    })
                    ->orWhereHas('client', function ($clientQ) use ($clientIds) {
                        $clientQ->whereIn('user_id', $clientIds)
                            ->whereHas('user.demandesAdhesions', function ($demandeQ) {
                                $demandeQ->whereIn('statut', ['validee', 'acceptee']);
                            });
                    });
            })->distinct('id')->count('id');

            // Sinistres
            $sinistresQuery = Sinistre::where('prestataire_id', $prestataire->id);
            $totalSinistres = $sinistresQuery->count();

            $sinistresParStatut = Sinistre::select('statut', DB::raw('COUNT(*) as total'))
                ->where('prestataire_id', $prestataire->id)
                ->groupBy('statut')
                ->pluck('total', 'statut');

            // Répartition mensuelle (12 derniers mois)
            $start = now()->startOfMonth()->subMonths(11);
            $end = now()->endOfMonth();
            $sinistresParMois = Sinistre::where('prestataire_id', $prestataire->id)
                ->whereBetween('created_at', [$start, $end])
                ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as mois"), DB::raw('COUNT(*) as total'))
                ->groupBy('mois')
                ->orderBy('mois')
                ->pluck('total', 'mois');

            // Compléter les mois manquants à 0
            $moisLabels = [];
            $moisData = [];
            $cursor = $start->copy();
            while ($cursor <= $end) {
                $key = $cursor->format('Y-m');
                $moisLabels[] = $cursor->translatedFormat('M Y');
                $moisData[] = (int) ($sinistresParMois[$key] ?? 0);
                $cursor->addMonth();
            }

            // Factures liées au prestataire
            $facturesQuery = Facture::where('prestataire_id', $prestataire->id);
            $totalFactures = $facturesQuery->count();
            $montantTotalFactures = (float) $facturesQuery->sum('montant_facture');
            $montantTotalTickets = (float) $facturesQuery->sum('ticket_moderateur');
            $facturesParStatut = Facture::select('statut', DB::raw('COUNT(*) as total'))
                ->where('prestataire_id', $prestataire->id)
                ->groupBy('statut')
                ->pluck('total', 'statut');

            $montantAutorise = (float) Facture::where('prestataire_id', $prestataire->id)
                ->where('statut', 'autorisee_comptable')
                ->sum(DB::raw('(montant_facture - COALESCE(ticket_moderateur,0))'));

            $montantRembourse = (float) Facture::where('prestataire_id', $prestataire->id)
                ->where('statut', 'rembourse')
                ->sum(DB::raw('(montant_facture - COALESCE(ticket_moderateur,0))'));

            // Taux de clôture sinistres
            $nbClotures = Sinistre::where('prestataire_id', $prestataire->id)->where('statut', 'cloture')->count();
            $tauxCloture = $totalSinistres > 0 ? round(($nbClotures / $totalSinistres) * 100, 2) : 0;

            // Top 5 assurés par nombre de sinistres
            $topAssures = Sinistre::where('prestataire_id', $prestataire->id)
                ->select('assure_id', DB::raw('COUNT(*) as total'))
                ->groupBy('assure_id')
                ->orderByDesc('total')
                ->with('assure:user_id,id,nom,prenoms')
                ->limit(5)
                ->get()
                ->map(function ($row) {
                    return [
                        'assure_id' => $row->assure_id,
                        'nom' => $row->assure->nom ?? null,
                        'prenoms' => $row->assure->prenoms ?? null,
                        'total_sinistres' => (int) $row->total,
                    ];
                });

            return ApiResponse::success([
                'assures' => [
                    'total_assignes' => (int) $assuresCount,
                ],
                'sinistres' => [
                    'total' => (int) $totalSinistres,
                    'par_statut' => $sinistresParStatut,
                    'par_mois' => [
                        'labels' => $moisLabels,
                        'data' => $moisData,
                    ],
                    'taux_cloture' => $tauxCloture,
                    'top_assures' => $topAssures,
                ],
                'factures' => [
                    'total' => (int) $totalFactures,
                    'par_statut' => $facturesParStatut,
                    'montant_total' => $montantTotalFactures,
                    'ticket_moderateur_total' => $montantTotalTickets,
                    'montant_autorise' => $montantAutorise,
                    'montant_rembourse' => $montantRembourse,
                ],
            ], 'Statistiques du tableau de bord prestataire');
        } catch (\Exception $e) {
            Log::error('Erreur dashboard prestataire', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);
            return ApiResponse::error('Erreur lors de la récupération des statistiques: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Valider une demande d'adhésion prestataire par un médecin contrôleur
     */
    public function validerPrestataire(int $id)
    {
        try {
            $medecinControleur = Auth::user();
            $demande = DemandeAdhesion::find($id);

            if (!$demande) {
                return ApiResponse::error('Demande d\'adhésion non trouvée', 404);
            }

            // Vérifier que la demande est en attente
            if (!$demande->isPending()) {
                return ApiResponse::error('Cette demande a déjà été traitée', 400);
            }

            DB::beginTransaction();

            // Valider la demande via le service
            $demande = $this->demandeAdhesionService->validerDemande($demande, $medecinControleur);
            $prestataire = Prestataire::where('user_id', $demande->user_id)->where('statut', StatutPrestataireEnum::ACTIF->value);

            // Envoyer l'email
            dispatch(new SendEmailJob($demande->user->email, 'Demande d\'adhésion prestataire validée', EmailType::ACCEPTED->value, [
                'demande' => $demande,
                'medecin_controleur' => $medecinControleur->personnel,
            ]));

            DB::commit();

            return ApiResponse::success([
                'demande_id' => $demande,
                'statut' => $demande->statut?->value ?? $demande->statut,
                'valide_par' => $medecinControleur->personnel->nom . ' ' . ($medecinControleur->personnel->prenoms ?? ''),
            ], 'Demande d\'adhésion prestataire validée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la validation de la demande prestataire', [
                'error' => $e->getMessage(),
                'demande_id' => $id,
                'medecin_controleur_id' => Auth::user()->personnel->id,
            ]);

            return ApiResponse::error('Erreur lors de la validation de la demande: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Récupérer tous les prestataires (pour les médecins contrôleurs)
     */
    public function index(Request $request)
    {
        try {
            $query = Prestataire::query();

            // Recherche par nom ou adresse
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('nom', 'like', "%{$search}%")
                        ->orWhere('adresse', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Filtrer par type de prestataire
            if ($request->has('type_prestataire')) {
                $query->where('type_prestataire', $request->type_prestataire);
            }

            // Filtrer par statut
            if ($request->has('statut')) {
                $query->where('statut', $request->statut);
            }

            $prestataires = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 15));

            return ApiResponse::success($prestataires, 'Liste des prestataires récupérée avec succès');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des prestataires', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return ApiResponse::error('Erreur lors de la récupération des prestataires: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Afficher les détails d'un prestataire
     */
    public function show(int $id)
    {
        try {
            $prestataire = Prestataire::find($id);

            if (!$prestataire) {
                return ApiResponse::error('Prestataire non trouvé', 404);
            }

            return ApiResponse::success($prestataire, 'Détails du prestataire récupérés avec succès');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération du prestataire', [
                'error' => $e->getMessage(),
                'prestataire_id' => $id,
                'user_id' => Auth::id()
            ]);

            return ApiResponse::error('Erreur lors de la récupération du prestataire: ' . $e->getMessage(), 500);
        }
    }


    public function getAssure()
    {
        $user = Auth::user()->load('prestataire');
        $prestataire = $user->prestataire;

        if (!$prestataire) {
            return ApiResponse::error('Prestataire non trouvé', 404);
        }

        // 1. Récupérer les client_contrats assignés à ce prestataire
        $clientContratsAssignes = ClientPrestataire::where('prestataire_id', $prestataire->id)
            ->where('statut', 'actif')
            ->with(['clientContrat' => function ($query) {
                $query->where('statut', 'actif')
                    ->where('date_debut', '<=', now())
                    ->where('date_fin', '>=', now());
            }])
            ->get()
            ->pluck('clientContrat')
            ->filter() // Enlever les null
            ->pluck('id')
            ->toArray();

        // if (empty($clientContratsAssignes)) {
        //     return ApiResponse::success($client, 'Aucun client ne vous est assigné');
        // }

        // 2. Récupérer les IDs des clients et contrats
        $clientContrats = ClientContrat::whereIn('id', $clientContratsAssignes)->get();
        $clientIds = $clientContrats->pluck('user_id')->toArray();

        // 3. Construire la requête de base pour les assurés
        $query = Assure::with([
            'assurePrincipal.user',
            'client.user',
            'beneficiaires',
            'user'
        ]);

        // 4. Filtrer par demande d'adhésion acceptée et contrat actif
        $query->where(function ($q) use ($clientIds) {
            // Cas 1: Assurés principaux (physiques ou entreprises)
            $q->where(function ($subQ) use ($clientIds) {
                $subQ->whereIn('user_id', $clientIds)
                    ->where('est_principal', true)
                    ->whereHas('user.demandesAdhesions', function ($demandeQ) {
                        $demandeQ->whereIn('statut', ['validee', 'acceptee']);
                    });
            })
                // Cas 2: Bénéficiaires des assurés principaux
                ->orWhereHas('assurePrincipal', function ($principalQ) use ($clientIds) {
                    $principalQ->whereIn('user_id', $clientIds)
                        ->where('est_principal', true)
                        ->whereHas('user.demandesAdhesions', function ($demandeQ) {
                            $demandeQ->whereIn('statut', ['validee', 'acceptee']);
                        });
                })
                // Cas 3: Employés d'entreprises assignées
                ->orWhereHas('client', function ($clientQ) use ($clientIds) {
                    $clientQ->whereIn('user_id', $clientIds)
                        ->whereHas('user.demandesAdhesions', function ($demandeQ) {
                            $demandeQ->whereIn('statut', ['validee', 'acceptee']);
                        });
                });
        });

        // 6. Pagination
        $assures = $query->orderByDesc('created_at')->get();

        // 7. Vérifier les contrats associés et logger
        foreach ($assures as $assure) {
            $contrat = $assure->getContratAssocie();

            if ($contrat) {
                Log::info("TypeContrat trouvé pour {$assure->nom} {$assure->prenoms}: " . $contrat->statut->value);
            } else {
                Log::warning("Aucun contrat pour {$assure->nom} {$assure->prenoms}");
            }
        }

        return ApiResponse::success(AssureResource::collection($assures), "Liste des assurés récupérée avec succès");
    }
}
