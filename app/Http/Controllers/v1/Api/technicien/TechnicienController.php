<?php

namespace App\Http\Controllers\v1\Api\technicien;

use App\Enums\RoleEnum;
use App\Enums\StatutContratEnum;
use App\Enums\StatutDemandeAdhesionEnum;
use App\Enums\StatutFactureEnum;
use App\Enums\StatutPrestataireEnum;
use App\Enums\StatutPropositionContratEnum;
use App\Enums\TypeDemandeurEnum;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\demande_adhesion\ProposerContratRequest;
use App\Models\DemandeAdhesion;
use App\Models\PropositionContrat;
use App\Models\Facture;
use App\Models\Personnel;
use App\Models\User;
use App\Jobs\SendEmailJob;
use App\Models\ClientContrat;
use App\Models\ClientPrestataire;
use App\Models\TypeContrat;
use App\Models\Prestataire;
use App\Models\Assure;
use App\Models\Entreprise;
use App\Http\Resources\AssureResource;
use App\Http\Resources\DemandeAdhesionResource;
use App\Models\Sinistre;
use App\Services\NotificationService;
use App\Traits\DemandeAdhesionDataTrait;
use App\Services\DemandeAdhesionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class TechnicienController extends Controller
{
    use DemandeAdhesionDataTrait;

    private $notificationService;
    private $demandeAdhesionService;
    public function __construct(
        NotificationService $notificationService,
        DemandeAdhesionService $demandeAdhesionService
    ) {
        $this->notificationService = $notificationService;
        $this->demandeAdhesionService = $demandeAdhesionService;
    }

    /**
     * Assigner un réseau de prestataires à un client
     */
    public function assignerReseauPrestataires(Request $request)
    {
        try {
            // Vérifier que l'utilisateur est un technicien
            if (!Auth::user()->hasRole('technicien')) {
                return ApiResponse::error('Accès non autorisé', 403);
            }

            $request->validate([
                'client_id' => 'required|exists:users,id',
                'prestataires_ids' => 'array',
            ]);

            $client = User::findOrFail($request->client_id);


            DB::beginTransaction();

            try {

                $clientContrat = ClientContrat::where('client_id', $client->id)->firstOrFail();

                foreach ($request->prestataires_ids as $prestataireId) {

                    // Vérifie si déjà assigné pour ce contrat
                    $dejaAssigne = ClientPrestataire::where('client_contrat_id', $clientContrat->id)
                        ->where('prestataire_id', $prestataireId)
                        ->exists();

                    if ($dejaAssigne) {
                        $prestataire = Prestataire::find($prestataireId);
                        return ApiResponse::error(
                            "Le prestataire '{$prestataire->raison_sociale}' est déjà assigné à ce client.",
                            422
                        );
                    }
                }



                if ($clientContrat === null) {
                    return ApiResponse::error('TypeContrat n\'ont enregistré');
                }

                // 2. Assigner les prestataires
                $prestatairesAssignes = [];
                foreach ($request->prestataires_ids as $prestataireId) {
                    Log::info('ids ' . $prestataireId);
                    // Vérifier que le prestataire existe
                    $prestataire = Prestataire::find($prestataireId);
                    if (!$prestataire) {
                        throw new \Exception("Prestataire ID {$prestataireId} non trouvé");
                    }

                    ClientPrestataire::create([
                        'client_contrat_id' => $clientContrat->id,
                        'prestataire_id' => $prestataireId,
                        'type_prestataire' => $prestataire->type_prestataire,
                        'statut' => 'actif'
                    ]);

                    $prestatairesAssignes[] = [
                        'id' => $prestataire->id,
                        'nom' => $prestataire->nom,
                        'adresse' => $prestataire->adresse
                    ];
                }

                // 3. Notification au client
                $this->notificationService->createNotification(
                    $client->id,
                    'Réseau de prestataires assigné',
                    "Un réseau de prestataires vous a été assigné. Vous pouvez maintenant vous soigner chez ces prestataires.",
                    'reseau_assigne',
                    [
                        'client_contrat_id' => $clientContrat->id,
                        'nombre_prestataires' => count($prestatairesAssignes),
                        'type' => 'reseau_assigne'
                    ]
                );

                DB::commit();

                return ApiResponse::success([
                    'client_contrat_id' => $clientContrat->id,
                    'prestataires_assignes' => $prestatairesAssignes,
                    'message' => 'Réseau de prestataires assigné avec succès'
                ], 'Réseau de prestataires assigné avec succès');
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'assignation du réseau de prestataires', [
                'error' => $e->getMessage(),
                'request_data' => $request->all(),
                'user_id' => Auth::id()
            ]);

            return ApiResponse::error('Erreur lors de l\'assignation du réseau', 500, $e->getMessage());
        }
    }

    /**
     * Statistiques complètes du technicien
     */
    public function technicienStats()
    {
        try {
            $user = Auth::user()->load('personne', 'personnel', 'roles');
            $technicien = $user->personnel;

            if (!$technicien) {
                return ApiResponse::error('Profil technicien non trouvé', 404);
            }

            // Statistiques des demandes d'adhésion
            $statsDemandesAdhesion = $this->getStatsDemandesAdhesion();

            // Statistiques des propositions de contrats
            $statsPropositionsContrats = $this->getStatsPropositionsContrats($technicien);

            // Statistiques des types de contrats
            $statsTypesContrats = $this->getStatsTypesContrats($technicien);

            // Statistiques des factures
            $statsFactures = $this->getStatsFacturesTechnicien($technicien);

            // Statistiques des clients
            $statsClients = $this->getStatsClients();

            // Évolutions mensuelles (pour graphiques)
            $evolutionsMensuelles = $this->getEvolutionsMensuellesTechnicien();

            return ApiResponse::success([
                'demandes_adhesion' => $statsDemandesAdhesion,
                'propositions_contrats' => $statsPropositionsContrats,
                'types_contrats' => $statsTypesContrats,
                'factures' => $statsFactures,
                'clients' => $statsClients,
                'evolutions_mensuelles' => $evolutionsMensuelles
            ], 'Statistiques du technicien récupérées avec succès');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des statistiques: ' . $e->getMessage());
            return ApiResponse::error('Erreur lors de la récupération des statistiques', 500, $e->getMessage());
        }
    }

    /**
     * Statistiques des demandes d'adhésion
     */
    private function getStatsDemandesAdhesion()
    {
        $demandes = DemandeAdhesion::all();
        $total = $demandes->count();

        // Répartition par statut
        $parStatut = $demandes->groupBy(function ($demande) {
            return $demande->statut?->value ?? 'Non spécifié';
        })->map(function ($group) use ($total) {
            $count = $group->count();
            return [
                'count' => $count,
                'pourcentage' => $total > 0 ? round(($count / $total) * 100, 2) : 0
            ];
        });

        // Répartition par type de demandeur
        $parType = $demandes->groupBy(function ($demande) {
            return $demande->type_demandeur?->value ?? 'Non spécifié';
        })->map(function ($group) use ($total) {
            $count = $group->count();
            return [
                'count' => $count,
                'pourcentage' => $total > 0 ? round(($count / $total) * 100, 2) : 0
            ];
        });

        return [
            'total' => $total,
            'en_attente' => $demandes->where('statut.value', StatutDemandeAdhesionEnum::EN_ATTENTE->value)->count(),
            'validees' => $demandes->where('statut.value', StatutDemandeAdhesionEnum::VALIDEE->value)->count(),
            'rejetees' => $demandes->where('statut.value', StatutDemandeAdhesionEnum::REJETEE->value)->count(),
            'taux_validation' => $total > 0 ? round(($demandes->where('statut.value', StatutDemandeAdhesionEnum::VALIDEE->value)->count() / $total) * 100, 2) : 0,
            'nouvelles_ce_mois' => $demandes->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count(),
            'repartition_par_statut' => $parStatut,
            'repartition_par_type' => $parType
        ];
    }

    /**
     * Statistiques des propositions de contrats
     */
    private function getStatsPropositionsContrats($technicien)
    {
        $propositions = PropositionContrat::all();
        $total = $propositions->count();

        $parStatut = $propositions->groupBy(function ($proposition) {
            return $proposition->statut?->value ?? 'Non spécifié';
        })->map(function ($group) use ($total) {
            $count = $group->count();
            return [
                'count' => $count,
                'pourcentage' => $total > 0 ? round(($count / $total) * 100, 2) : 0
            ];
        });

        return [
            'total' => $total,
            'proposees' => $propositions->where('statut.value', StatutPropositionContratEnum::PROPOSEE->value)->count(),
            'acceptees' => $propositions->where('statut.value', StatutPropositionContratEnum::ACCEPTEE->value)->count(),
            'refusees' => $propositions->where('statut.value', StatutPropositionContratEnum::REFUSEE->value)->count(),
            'expirees' => $propositions->where('statut.value', StatutPropositionContratEnum::EXPIREE->value)->count(),
            'taux_acceptation' => $total > 0 ? round(($propositions->where('statut.value', StatutPropositionContratEnum::ACCEPTEE->value)->count() / $total) * 100, 2) : 0,
            'repartition_par_statut' => $parStatut
        ];
    }

    /**
     * Statistiques des types de contrats
     */
    private function getStatsTypesContrats($technicien)
    {
        $typesContrats = $technicien->contrats;
        $total = $typesContrats->count();

        return [
            'total' => $total,
            'actifs' => $typesContrats->where('est_actif', true)->count(),
            'inactifs' => $typesContrats->where('est_actif', false)->count(),
            'taux_activation' => $total > 0 ? round(($typesContrats->where('est_actif', true)->count() / $total) * 100, 2) : 0,
            'prime_moyenne' => $total > 0 ? round($typesContrats->avg('prime_standard'), 2) : 0,
            'prime_totale' => $typesContrats->sum('prime_standard')
        ];
    }

    /**
     * Statistiques des factures du technicien
     */
    private function getStatsFacturesTechnicien($technicien)
    {
        $factures = Facture::all();
        $total = $factures->count();

        $facturesValideesTechnicien = $technicien->facturesValideesTechnicien()->count();
        $aValiderParTechnicien = $factures->filter(function ($facture) {
            return !$facture->isValidatedByTechnicien();
        })->count();

        return [
            'total' => $total,
            'validees_par_technicien' => $facturesValideesTechnicien,
            'a_valider_par_technicien' => $aValiderParTechnicien,
            'en_attente_medecin' => $factures->filter(function ($facture) {
                return $facture->isValidatedByTechnicien() && !$facture->isValidatedByMedecin();
            })->count()
        ];
    }

    /**
     * Statistiques des clients
     */
    private function getStatsClients()
    {
        $clients = User::whereHas('roles', function ($q) {
            $q->where('name', RoleEnum::CLIENT->value);
        })->get();

        $total = $clients->count();

        return [
            'total' => $total,
            'actifs' => $clients->where('est_actif', true)->count(),
            'inactifs' => $clients->where('est_actif', false)->count(),
            'taux_activation' => $total > 0 ? round(($clients->where('est_actif', true)->count() / $total) * 100, 2) : 0
        ];
    }

    /**
     * Évolution mensuelle (12 derniers mois) - Pour graphiques
     */
    private function getEvolutionsMensuellesTechnicien()
    {
        $evolution = [];
        $maintenant = now();

        for ($i = 11; $i >= 0; $i--) {
            $date = $maintenant->copy()->subMonths($i);
            $moisDebut = $date->copy()->startOfMonth();
            $moisFin = $date->copy()->endOfMonth();

            // Demandes d'adhésion ce mois
            $demandesCeMois = DemandeAdhesion::whereBetween('created_at', [$moisDebut, $moisFin])->count();

            $demandesValideesCeMois = DemandeAdhesion::whereBetween('created_at', [$moisDebut, $moisFin])
                ->where('statut', StatutDemandeAdhesionEnum::VALIDEE)
                ->count();

            $demandesRejeteesCeMois = DemandeAdhesion::whereBetween('created_at', [$moisDebut, $moisFin])
                ->where('statut', StatutDemandeAdhesionEnum::REJETEE)
                ->count();

            // Propositions de contrats ce mois
            $propositionsCeMois = PropositionContrat::whereBetween('created_at', [$moisDebut, $moisFin])->count();

            $propositionsAccepteesCeMois = PropositionContrat::whereBetween('created_at', [$moisDebut, $moisFin])
                ->where('statut', StatutPropositionContratEnum::ACCEPTEE)
                ->count();

            // Factures validées par technicien ce mois
            $facturesValideesCeMois = Facture::whereBetween('valide_par_technicien_a', [$moisDebut, $moisFin])
                ->whereNotNull('valide_par_technicien_a')
                ->count();

            // Clients créés ce mois
            $clientsCeMois = User::whereHas('roles', function ($q) {
                $q->where('name', RoleEnum::CLIENT->value);
            })->whereBetween('created_at', [$moisDebut, $moisFin])->count();

            $evolution[] = [
                'mois' => $date->format('Y-m'),
                'mois_nom' => $date->format('M Y'),
                'mois_complet' => $date->format('F Y'),
                'demandes_recues' => $demandesCeMois,
                'demandes_validees' => $demandesValideesCeMois,
                'demandes_rejetees' => $demandesRejeteesCeMois,
                'propositions_envoyees' => $propositionsCeMois,
                'propositions_acceptees' => $propositionsAccepteesCeMois,
                'factures_validees' => $facturesValideesCeMois,
                'clients_crees' => $clientsCeMois,
                'taux_validation' => $demandesCeMois > 0
                    ? round(($demandesValideesCeMois / $demandesCeMois) * 100, 2)
                    : 0,
                'taux_rejet' => $demandesCeMois > 0
                    ? round(($demandesRejeteesCeMois / $demandesCeMois) * 100, 2)
                    : 0
            ];
        }

        return $evolution;
    }

    /**
     * Détails d'une demande d'adhésion
     */
    public function showDemande($id)
    {
        $user = Auth::user();
        $technicien = $user->personnel;

        if (!$technicien || !$technicien->isTechnicien()) {
            return ApiResponse::error('Accès non autorisé', 403);
        }

        $demande = $this->loadDemandeWithRelations($id);

        if (!$demande) {
            return ApiResponse::error('Demande d\'adhésion non trouvée', 404);
        }

        return ApiResponse::success($this->prepareDemandeData($demande), 'Demande d\'adhésion récupérée avec succès');
    }

    /**
     * Liste des demandes d'adhésion
     */
    public function demandesAdhesion(Request $request)
    {
        $user = Auth::user()->load('personne', 'personnel', 'roles');
        $technicien = $user->personnel;

        if (!$technicien || !$technicien->isTechnicien()) {
            return ApiResponse::error('Accès non autorisé', 403);
        }

        $query = DemandeAdhesion::with([
            'user',
            'assurePrincipal.user.personne',
            'assurePrincipal.beneficiaires.user.personne',
            'propositionsContrat.contrat',
            'reponsesQuestions.question', // Charger toutes les réponses
        ]);
        $this->demandeAdhesionService->applyRoleFilters($query, $user);
        $demandes = $query->orderByDesc('created_at')->get();

        return ApiResponse::success(DemandeAdhesionResource::collection($demandes), 'Liste des demandes d\'adhésion récupérée avec succès');
    }

    /**
     * Valider une demande d'adhésion
     */
    public function validerDemande(Request $request, $id)
    {
        $user = Auth::user();
        $technicien = $user->personnel;

        $validated = $request->validate([
            'motif_validation' => 'nullable|string|max:500',
            'notes_techniques' => 'nullable|string|max:1000',
        ]);

        $demande = DemandeAdhesion::find($id);

        if (!$demande) {
            return ApiResponse::error('Demande d\'adhésion non trouvée', 404);
        }

        if ($demande->statut !== StatutDemandeAdhesionEnum::EN_ATTENTE) {
            return ApiResponse::error('Cette demande ne peut plus être validée', 400);
        }

        $demande->update([
            'statut' => StatutDemandeAdhesionEnum::VALIDEE,
            'valide_par_id' => $technicien->id,
            'valide_a' => now(),
            'motif_validation' => $validated['motif_validation'],
            'notes_techniques' => $validated['notes_techniques'],
        ]);

        // Notifier le client via l'application
        $notificationService = app(NotificationService::class);
        $notificationService->createNotification(
            $demande->user->id,
            'Demande d\'adhésion validée',
            "Votre demande d'adhésion a été validée par notre équipe technique. Vous pouvez maintenant procéder à la souscription.",
            'demande_validee',
            [
                'demande_id' => $demande->id,
                'valide_par' => $technicien->nom . ' ' . ($technicien->prenoms ?? ''),
                'date_validation' => now()->format('d/m/Y à H:i'),
                'motif_validation' => $validated['motif_validation'] ?? null,
                'type' => 'demande_validee'
            ]
        );

        // Envoyer l'email
        dispatch(new SendEmailJob(
            $demande->user->email,
            'Demande d\'adhésion validée',
            \App\Enums\EmailType::ACCEPTED->value,
            [
                'demande' => $demande,
                'technicien' => $technicien,
            ]
        ));

        return ApiResponse::success($demande, 'Demande d\'adhésion validée avec succès');
    }

    /**
     * Proposer un contrat
     */
    public function proposerContrat(ProposerContratRequest $request, $id)
    {
        $user = Auth::user();
        $technicien = $user->personnel;

        $validated = $request->validated();

        $demande = DemandeAdhesion::find($id);
        $dateProposition = now();

        if (!$demande) {
            return ApiResponse::error('Demande d\'adhésion non trouvée', 404);
        }

        try {
            DB::beginTransaction();
            $propositionContrat = PropositionContrat::create([
                'demande_adhesion_id' => $demande->id,
                'contrat_id' => $validated['contrat_id'],
                'commentaires_technicien' => $validated['commentaires_technicien'] ?? null,
                'technicien_id' => $technicien->id,
                'statut' => StatutPropositionContratEnum::PROPOSEE->value,
                'date_proposition' => $dateProposition,
                'date_acceptation' => null,
                'date_refus' => null,
            ]);

            $demande->update([
                'statut' => StatutDemandeAdhesionEnum::PROPOSEE->value,
            ]);

            // Notifier le client via l'application
            $notificationService = app(NotificationService::class);

            $notificationService->createNotification(
                $demande->user->id,
                'Proposition de contrat reçue',
                "Un technicien a analysé votre demande et vous propose un contrat.",
                'contrat_propose',
                [
                    'demande_id' => $demande->id,
                    'contrat_id' => $propositionContrat->id,
                    'libelle' => $propositionContrat->contrat->libelle,
                    'prime_standard' => $propositionContrat->contrat->prime_standard,
                    'pourcentage_gestion' => $propositionContrat->contrat->frais_gestion,
                    'commentaires_technicien' => $propositionContrat->commentaires_technicien,
                    'propose_par' => $technicien->nom . ' ' . ($technicien->prenoms ?? ''),
                    'date_proposition' => $dateProposition->format('d/m/Y à H:i'),
                    'type' => 'contrat_propose'
                ]
            );

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la proposition de contrat', ['error' => $e->getMessage()]);
            return ApiResponse::error('Erreur lors de la proposition de contrat', 500, $e->getMessage());
        }

        return ApiResponse::success($propositionContrat, 'Proposition de contrat proposée avec succès');
    }

    /**
     * Liste des factures à valider
     */
    public function factures(Request $request)
    {
        $user = Auth::user();
        $technicien = $user->personnel;

        if (!$technicien || !$technicien->isTechnicien()) {
            return ApiResponse::error('Accès non autorisé', 403);
        }

        $query = Facture::with(['prestataire', 'assure']);

        // Filtres
        if ($request->has('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->has('prestataire_id')) {
            $query->where('prestataire_id', $request->prestataire_id);
        }

        if ($request->has('date_debut')) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }

        if ($request->has('date_fin')) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }

        $factures = $query->paginate($request->get('per_page', 10));

        return ApiResponse::success($factures, 'Liste des factures récupérée avec succès');
    }

    /**
     * Valider une facture
     */
    public function validerFacture(Request $request, $id)
    {
        $user = Auth::user();
        $technicien = $user->personnel;

        if (!$technicien || !$technicien->isTechnicien()) {
            return ApiResponse::error('Accès non autorisé', 403);
        }

        $facture = Facture::find($id);

        if (!$facture) {
            return ApiResponse::error('Facture non trouvée', 404);
        }

        if ($facture->statut !== StatutFactureEnum::EN_ATTENTE) {
            return ApiResponse::error('Cette facture ne peut plus être validée', 400);
        }

        $facture->update([
            'statut' => StatutFactureEnum::VALIDEE_TECHNICIEN,
            'technicien_id' => $technicien->id,
            'valide_par_technicien_a' => now(),
        ]);

        return ApiResponse::success($facture, 'Facture validée avec succès');
    }

    /**
     * Rejeter une facture
     */
    public function rejeterFacture(Request $request, $id)
    {
        $user = Auth::user();
        $technicien = $user->personnel;

        if (!$technicien || !$technicien->isTechnicien()) {
            return ApiResponse::error('Accès non autorisé', 403);
        }

        $validated = $request->validate([
            'motif_rejet' => 'required|string|max:500',
        ]);

        $facture = Facture::find($id);

        if (!$facture) {
            return ApiResponse::error('Facture non trouvée', 404);
        }

        if ($facture->statut !== StatutFactureEnum::EN_ATTENTE) {
            return ApiResponse::error('Cette facture ne peut plus être rejetée', 400);
        }

        $facture->update([
            'statut' => StatutFactureEnum::REJETEE,
            'rejetee_par_id' => $technicien->id,
            'rejetee_a' => now(),
            'motif_rejet_technicien' => $validated['motif_rejet'],
        ]);

        return ApiResponse::success($facture, 'Facture rejetée avec succès');
    }

    /**
     * Détails d'une facture
     */
    public function showFacture($id)
    {
        $user = Auth::user();
        $technicien = $user->personnel;

        if (!$technicien || !$technicien->isTechnicien()) {
            return ApiResponse::error('Accès non autorisé', 403);
        }

        $facture = Facture::with([
            'prestataire',
            'assure',
            'technicien',
            'medecin'
        ])->find($id);

        if (!$facture) {
            return ApiResponse::error('Facture non trouvée', 404);
        }

        return ApiResponse::success($facture, 'Facture récupérée avec succès');
    }

    /**
     * Récupérer la liste des clients pour le technicien (avec recherche)
     */
    public function getClientsTechnicien(Request $request)
    {
        try {
            // Vérifier que l'utilisateur est un technicien
            if (!Auth::user()->hasRole('technicien')) {
                return ApiResponse::error('Accès non autorisé', 403);
            }

            $query = DemandeAdhesion::with(['user', 'user.assure', 'user.entreprise'])
                ->whereIn('type_demandeur', [TypeDemandeurEnum::CLIENT->value])
                ->whereIn('statut', [
                    StatutDemandeAdhesionEnum::EN_ATTENTE->value,
                    StatutDemandeAdhesionEnum::PROPOSEE->value,
                    StatutDemandeAdhesionEnum::ACCEPTEE->value
                ]);

            // Recherche par nom ou email
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('nom', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $demandes = $query->get()->map(function ($demande) {
                return [
                    'id' => $demande->id,
                    'client_id' => $demande->user->id,
                    'nom' => $demande->user->nom ?? $demande->user->name,
                    'email' => $demande->user->email,
                    'type_demandeur' => $demande->type_demandeur?->value ?? $demande->type_demandeur,
                    'statut' => $demande->statut?->value ?? $demande->statut,
                    'date_soumission' => $demande->created_at->format('Y-m-d'),
                    'duree_attente' => $demande->created_at->diffForHumans()
                ];
            });

            return ApiResponse::success($demandes, 'Liste des clients récupérée avec succès');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des clients', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return ApiResponse::error('Erreur lors de la récupération des clients: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Récupérer les statistiques des clients pour le technicien
     */
    public function getStatistiquesClients()
    {
        try {
            // Vérifier que l'utilisateur est un technicien
            if (!Auth::user()->hasRole('technicien')) {
                return ApiResponse::error('Accès non autorisé', 403);
            }

            $totalClients = DemandeAdhesion::whereIn('type_demandeur', [
                TypeDemandeurEnum::CLIENT->value
            ])->count();

            $clientsEnAttente = DemandeAdhesion::whereIn('type_demandeur', [
                TypeDemandeurEnum::CLIENT->value
            ])->where('statut', StatutDemandeAdhesionEnum::EN_ATTENTE->value)->count();

            $clientsEnProposition = DemandeAdhesion::whereIn('type_demandeur', [
                TypeDemandeurEnum::CLIENT->value
            ])->where('statut', StatutDemandeAdhesionEnum::PROPOSEE->value)->count();

            $clientsAcceptes = DemandeAdhesion::whereIn('type_demandeur', [
                TypeDemandeurEnum::CLIENT->value
            ])->where('statut', StatutDemandeAdhesionEnum::ACCEPTEE->value)->count();

            $repartitionParType = [
                'client' => DemandeAdhesion::where('type_demandeur', TypeDemandeurEnum::CLIENT->value)->count(),
                'entreprise' => DemandeAdhesion::where('type_demandeur')->count(),
            ];

            $statistiques = [
                'total_clients' => $totalClients,
                'clients_en_attente' => $clientsEnAttente,
                'clients_en_proposition' => $clientsEnProposition,
                'clients_acceptes' => $clientsAcceptes,
                'repartition_par_type' => $repartitionParType,
                'taux_acceptation' => $totalClients > 0 ? round(($clientsAcceptes / $totalClients) * 100, 2) : 0
            ];

            return ApiResponse::success($statistiques, 'Statistiques des clients récupérées avec succès');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des statistiques des clients', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return ApiResponse::error('Erreur lors de la récupération des statistiques: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Récupérer les détails d'un client spécifique
     */
    public function showClientDetails(int $id)
    {
        try {
            // Vérifier que l'utilisateur est un technicien
            if (!Auth::user()->hasRole('technicien')) {
                return ApiResponse::error('Accès non autorisé', 403);
            }

            $demande = DemandeAdhesion::with([
                'user',
                'user.assure',
                'user.entreprise',
                'reponsesQuestionnaire.question'
            ])->find($id);

            if (!$demande) {
                return ApiResponse::error('Client non trouvé', 404);
            }

            // Vérifier que c'est bien un client (physique ou entreprise)
            if (!in_array($demande->type_demandeur, [
                TypeDemandeurEnum::CLIENT->value
            ])) {
                return ApiResponse::error('Ce n\'est pas un client valide', 400);
            }

            $clientData = [
                'id' => $demande->id,
                'client_id' => $demande->user->id,
                'nom' => $demande->user->nom ?? $demande->user->name,
                'email' => $demande->user->email,
                'contact' => $demande->user->contact,
                'adresse' => $demande->user->adresse,
                'type_demandeur' => $demande->type_demandeur?->value ?? $demande->type_demandeur,
                'statut' => $demande->statut?->value ?? $demande->statut,
                'date_soumission' => $demande->created_at,
                'date_mise_a_jour' => $demande->updated_at,
                'duree_attente' => $demande->created_at->diffForHumans(),
                'reponses_questionnaire' => $demande->reponsesQuestionnaire->map(function ($reponse) {
                    return [
                        'question_id' => $reponse->question_id,
                        'question_libelle' => $reponse->question->libelle,
                        'reponse_text' => $reponse->reponse_text,
                        'reponse_number' => $reponse->reponse_number,
                        'reponse_bool' => $reponse->reponse_bool,
                        'reponse_date' => $reponse->reponse_date,
                        'reponse_fichier' => $reponse->reponse_fichier,
                    ];
                })
            ];

            // Ajouter les données spécifiques selon le type
            if ($demande->type_demandeur === TypeDemandeurEnum::CLIENT->value) {
                $clientData['assure'] = $demande->user->assure ? [
                    'id' => $demande->user->assure->id,
                    'nom' => $demande->user->assure->nom,
                    'prenoms' => $demande->user->assure->prenoms,
                    'date_naissance' => $demande->user->assure->date_naissance,
                    'sexe' => $demande->user->assure->sexe,
                    'profession' => $demande->user->assure->profession,
                    'photo_url' => $demande->user->assure->photo_url,
                ] : null;
            }

            return ApiResponse::success($clientData, 'Détails du client récupérés avec succès');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération du client', [
                'error' => $e->getMessage(),
                'client_id' => $id,
                'user_id' => Auth::id()
            ]);

            return ApiResponse::error('Erreur lors de la récupération du client: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Récupérer les clients avec contrats acceptés pour l'assignation de prestataires
     */
    public function getClientsAvecContratsAcceptes(Request $request)
    {
        try {
            if (!Auth::user()->hasRole('technicien')) {
                return ApiResponse::error('Accès non autorisé', 403);
            }


            // Récupérer les propositions de contrat acceptées
            $query = PropositionContrat::with([
                'demandeAdhesion.user.assure',
                'contrat'
            ])
                ->where('statut', StatutPropositionContratEnum::ACCEPTEE->value)
                ->whereHas('demandeAdhesion', function ($q) {
                    $q->whereIn('type_demandeur', [
                        TypeDemandeurEnum::CLIENT->value,
                    ]);
                });

            // Recherche par nom ou email
            if ($request->filled('search')) {
                $search = $request->search;
                $query->whereHas('demandeAdhesion.user', function ($q) use ($search) {
                    $q->where('nom', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('entreprise', function ($eq) use ($search) {
                            $eq->where('raison_sociale', 'like', "%{$search}%");
                        });
                });
            }

            $propositions = $query->get();

            $clients = $propositions->map(function ($proposition) {
                $demande = $proposition->demandeAdhesion;
                $user = $demande->user;
                $client = $user->client;

                if (!$client) {
                    return ApiResponse::error('Client non trouvé pour cet utilisateur', 404);
                }

                // Vérifier si des prestataires sont déjà assignés
                $prestatairesAssignes = ClientContrat::where('client_id', $client->id)
                    ->where('type_contrat_id', $proposition->contrat->id)
                    ->whereHas('prestataires', function ($q) {
                        $q->where('statut', StatutPrestataireEnum::ACTIF->value);
                    })
                    ->exists();

                return [
                    'id' => $user->id,
                    'nom' => $user->assure->nom ?? $user->entreprise->raison_sociale,
                    'prenoms' => $user->assure->prenoms ?? null,
                    'email' => $user->email,
                    'contact' => $user->contact,
                    'type_client' => $demande->type_demandeur->value,
                    'raison_sociale' => $user->entreprise->raison_sociale ?? null,
                    'contrat' => [
                        'id' => $proposition->contrat->id,
                        'libelle' => $proposition->contrat->libelle,
                        'date_acceptation' => $proposition->date_acceptation,
                        'prime_standard' => $proposition->contrat->prime_standard,
                        'couverture' => $proposition->contrat->couverture,
                    ],
                    'prestataires_assignes' => $prestatairesAssignes,
                    'nombre_employes' => $user->entreprise ?
                        $user->entreprise->assures()->where('est_principal', true)->count() : null,
                    'created_at' => $demande->created_at,
                ];
            });

            return ApiResponse::success($clients, 'Clients avec contrats acceptés récupérés avec succès');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des clients avec contrats acceptés', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return ApiResponse::error('Erreur lors de la récupération: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Liste des sinistres
     */
    public function sinistres(Request $request)
    {
        try {
            if (!Auth::user()->hasRole('technicien')) {
                return ApiResponse::error('Accès non autorisé', 403);
            }

            $query = Sinistre::with(['assure.user', 'facture']);

            // Filtres
            if ($request->filled('statut')) {
                $query->where('statut', $request->statut);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->whereHas('assure', function ($q) use ($search) {
                    $q->where('nom', 'like', '%' . $search . '%')
                        ->orWhere('prenoms', 'like', '%' . $search . '%');
                });
            }

            $sinistres = $query->latest()->get();

            return ApiResponse::success($sinistres, 'Liste des sinistres récupérée avec succès');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des sinistres: ' . $e->getMessage());
            return ApiResponse::error('Erreur lors de la récupération des sinistres', 500, $e->getMessage());
        }
    }

    /**
     * Détails d'un sinistre
     */
    public function showSinistre($id)
    {
        try {
            if (!Auth::user()->hasRole('technicien')) {
                return ApiResponse::error('Accès non autorisé', 403);
            }

            $sinistre = Sinistre::with(['assure.user', 'facture.prestataire', 'facture.lignesFacture'])
                ->find($id);

            if (!$sinistre) {
                return ApiResponse::error('Sinistre non trouvé', 404);
            }

            return ApiResponse::success($sinistre, 'Détails du sinistre récupérés avec succès');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération du sinistre: ' . $e->getMessage());
            return ApiResponse::error('Erreur lors de la récupération du sinistre', 500, $e->getMessage());
        }
    }

    /**
     * Récupérer tous les prestataires validés pour l'assignation
     */
    public function getPrestatairesPourAssignation(Request $request)
    {
        try {
            if (!Auth::user()->hasRole('technicien')) {
                return ApiResponse::error('Accès non autorisé', 403);
            }


            $query = Prestataire::with('user')
                ->where('statut', StatutPrestataireEnum::ACTIF->value);

            // Recherche par nom ou adresse
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('raison_sociale', 'like', "%{$search}%")
                        ->orWhere('adresse', 'like', "%{$search}%");
                });
            }

            // Filtrer par type de prestataire
            if ($request->filled('type_prestataire')) {
                $query->where('type_prestataire', $request->type_prestataire);
            }

            $prestataires = $query->get();

            $prestatairesList = $prestataires->map(function ($prestataire) {
                return [
                    'id' => $prestataire->id,
                    'raison_sociale' => $prestataire->raison_sociale,
                    'type_prestataire' => $prestataire->type_prestataire,
                    'adresse' => $prestataire->user->adresse,
                    'contact' => $prestataire->user->contact ?? null,
                    'email' => $prestataire->user->email,
                    'statut' => $prestataire->statut,
                    'nombre_clients_assignes' => ClientPrestataire::where('prestataire_id', $prestataire->id)
                        ->where('statut', StatutPrestataireEnum::ACTIF->value)
                        ->count(),
                    'created_at' => $prestataire->created_at,
                ];
            });

            return ApiResponse::success($prestatairesList, 'Prestataires pour assignation récupérés avec succès');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des prestataires pour assignation', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return ApiResponse::error('Erreur lors de la récupération: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Voir les assignations existantes d'un client
     */
    public function getAssignationsClient($clientId)
    {
        try {
            if (!Auth::user()->hasRole('technicien')) {
                return ApiResponse::error('Accès non autorisé', 403);
            }

            $client = User::with('client')->find($clientId);
            if (!$client || !$client->client) {
                return ApiResponse::error('Client non trouvé', 404);
            }

            // Récupérer le contrat actif du client
            $clientContrat = ClientContrat::where('client_id', $client->client->id)
                ->where('statut', StatutContratEnum::ACTIF->value)
                ->first();

            if (!$clientContrat) {
                return ApiResponse::success([
                    'client' => [
                        'id' => $client->id,
                        'nom' => optional($client->personne)->nom ?? $client->email,
                        'email' => $client->email,
                    ],
                    "client_prestataire" => [],
                    'message' => 'Ce client n\'a pas de contrat actif'
                ], 'Aucun contrat actif trouvé pour ce client');
            }

            $clientPrestataire = ClientPrestataire::where('client_contrat_id', $clientContrat->id)
                ->with(['clientContrat', 'prestataire'])
                ->where('statut', StatutPrestataireEnum::ACTIF->value)
                ->get();


            return ApiResponse::success([
                'client' => [
                    'id' => $client->id,
                    'nom' => optional($client->personne)->nom ?? $client->email,
                    'email' => $client->email,
                ],
                "client_prestataire" => $clientPrestataire,
            ], 'Assignations du client récupérées avec succès');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des assignations client', [
                'error' => $e->getMessage(),
                'client_id' => $clientId,
                'user_id' => Auth::id()
            ]);

            return ApiResponse::error('Erreur lors de la récupération: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Récupérer tous les assurés selon le rôle de l'utilisateur
     */
    public function getAllAssures(Request $request)
    {
        try {
            $user = Auth::user();

            // Vérifier les permissions selon le rôle
            $allowedRoles = ['technicien', 'medecin_controleur', 'comptable', 'admin_global', 'gestionnaire'];
            $hasPermission = false;

            foreach ($allowedRoles as $role) {
                if ($user->hasRole($role)) {
                    $hasPermission = true;
                    break;
                }
            }

            if (!$hasPermission) {
                return ApiResponse::error('Accès non autorisé', 403);
            }

            // Construire la requête de base
            $query = Assure::with([
                'assurePrincipal.user',
                'entreprise.user',
                'beneficiaires',
                'user'
            ]);

            // Filtres selon le rôle
            if ($user->hasRole('gestionnaire') && $user->entreprise) {
                // Gestionnaire : seulement les assurés de son entreprise
                $query->where('entreprise_id', $user->entreprise->id);
            }

            // Filtres de recherche
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->whereRaw('LOWER(nom) LIKE ?', ['%' . strtolower($search) . '%'])
                        ->orWhereRaw('LOWER(prenoms) LIKE ?', ['%' . strtolower($search) . '%'])
                        ->orWhereRaw('LOWER(email) LIKE ?', ['%' . strtolower($search) . '%']);
                });
            }

            if ($request->filled('sexe')) {
                $query->where('sexe', $request->sexe);
            }

            if ($request->filled('est_principal')) {
                $query->where('est_principal', $request->est_principal);
            }

            if ($request->filled('entreprise_id')) {
                $query->where('entreprise_id', $request->entreprise_id);
            }

            // Pagination
            $assures = $query->orderByDesc('created_at')->get();

            // Formater les données avec AssureResource
            $paginatedData = AssureResource::collection($assures);

            return ApiResponse::success($paginatedData, "Liste des assurés récupérée avec succès");
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des assurés', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'user_roles' => $user->roles->pluck('name')
            ]);

            return ApiResponse::error('Erreur lors de la récupération des assurés: ' . $e->getMessage(), 500);
        }
    }
}
