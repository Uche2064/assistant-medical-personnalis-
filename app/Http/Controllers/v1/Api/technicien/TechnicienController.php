<?php

namespace App\Http\Controllers\v1\Api\technicien;

use App\Enums\RoleEnum;
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
use App\Models\Contrat;
use App\Models\Prestataire;
use App\Services\NotificationService;
use App\Traits\DemandeAdhesionDataTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TechnicienController extends Controller
{
    use DemandeAdhesionDataTrait;

    private $notificationService;

    public function __construct(
        NotificationService $notificationService,
    ) {
        $this->notificationService = $notificationService;
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

                $clientContrat = ClientContrat::where('user_id', $client->id)->firstOrFail();

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
                    return ApiResponse::error('Contrat n\'ont enregistré');
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

            return ApiResponse::error('Erreur lors de l\'assignation du réseau: ' . $e->getMessage(), 500);
        }
    }


    /**
     * Dashboard du technicien
     */
    public function dashboard()
    {
        $user = Auth::user();
        $technicien = $user->personnel;

        if (!$technicien || !$technicien->isTechnicien()) {
            return ApiResponse::error('Accès non autorisé', 403);
        }

        // Statistiques du technicien
        $stats = [
            'total_demandes' => DemandeAdhesion::count(),
            'demandes_en_attente' => DemandeAdhesion::where('statut', StatutDemandeAdhesionEnum::EN_ATTENTE)->count(),
            'demandes_validees' => DemandeAdhesion::where('statut', StatutDemandeAdhesionEnum::VALIDEE)->count(),
            'demandes_rejetees' => DemandeAdhesion::where('statut', StatutDemandeAdhesionEnum::REJETEE)->count(),
            'contrats_proposes' => $technicien->contrats()->count(),
            'contrats_acceptes' => $technicien->contrats()->where('statut', 'accepte')->count(),
            'factures_validees' => $technicien->facturesValideesTechnicien()->count(),
        ];

        // Demandes récentes
        $demandesRecentes = DemandeAdhesion::with(['user', 'client', 'entreprise'])
            ->latest()
            ->take(5)
            ->get();

        // Factures en attente de validation
        $facturesEnAttente = Facture::where('statut', StatutFactureEnum::EN_ATTENTE)
            ->with(['prestataire', 'assure'])
            ->latest()
            ->take(5)
            ->get();

        return ApiResponse::success([
            'technicien' => [
                'id' => $technicien->id,
                'nom' => $technicien->nom,
                'prenoms' => $technicien->prenoms,
                'email' => $technicien->email,
            ],
            'statistiques' => $stats,
            'demandes_recentes' => $demandesRecentes,
            'factures_en_attente' => $facturesEnAttente,
        ], 'Dashboard technicien récupéré avec succès');
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
        $user = Auth::user();
        $technicien = $user->personnel;

        if (!$technicien || !$technicien->isTechnicien()) {
            return ApiResponse::error('Accès non autorisé', 403);
        }

        $query = DemandeAdhesion::with(['user', 'client', 'entreprise']);

        // Filtres
        if ($request->has('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->has('type_demandeur')) {
            $query->where('type_demandeur', $request->type_demandeur);
        }

        $demandes = $query->paginate($request->get('per_page', 10));

        return ApiResponse::success($demandes, 'Liste des demandes d\'adhésion récupérée avec succès');
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

        $validated = $request->validate([
            'notes_validation' => 'nullable|string|max:500',
        ]);

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
            'notes_technicien' => $validated['notes_validation'],
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
                ->whereIn('type_demandeur', [TypeDemandeurEnum::PHYSIQUE->value, TypeDemandeurEnum::ENTREPRISE->value])
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
                TypeDemandeurEnum::PHYSIQUE->value,
                TypeDemandeurEnum::ENTREPRISE->value
            ])->count();

            $clientsEnAttente = DemandeAdhesion::whereIn('type_demandeur', [
                TypeDemandeurEnum::PHYSIQUE->value,
                TypeDemandeurEnum::ENTREPRISE->value
            ])->where('statut', StatutDemandeAdhesionEnum::EN_ATTENTE->value)->count();

            $clientsEnProposition = DemandeAdhesion::whereIn('type_demandeur', [
                TypeDemandeurEnum::PHYSIQUE->value,
                TypeDemandeurEnum::ENTREPRISE->value
            ])->where('statut', StatutDemandeAdhesionEnum::PROPOSEE->value)->count();

            $clientsAcceptes = DemandeAdhesion::whereIn('type_demandeur', [
                TypeDemandeurEnum::PHYSIQUE->value,
                TypeDemandeurEnum::ENTREPRISE->value
            ])->where('statut', StatutDemandeAdhesionEnum::ACCEPTEE->value)->count();

            $repartitionParType = [
                'physique' => DemandeAdhesion::where('type_demandeur', TypeDemandeurEnum::PHYSIQUE->value)->count(),
                'entreprise' => DemandeAdhesion::where('type_demandeur', TypeDemandeurEnum::ENTREPRISE->value)->count(),
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
                TypeDemandeurEnum::PHYSIQUE->value,
                TypeDemandeurEnum::ENTREPRISE->value
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
            if ($demande->type_demandeur === TypeDemandeurEnum::PHYSIQUE->value) {
                $clientData['assure'] = $demande->user->assure ? [
                    'id' => $demande->user->assure->id,
                    'nom' => $demande->user->assure->nom,
                    'prenoms' => $demande->user->assure->prenoms,
                    'date_naissance' => $demande->user->assure->date_naissance,
                    'sexe' => $demande->user->assure->sexe,
                    'profession' => $demande->user->assure->profession,
                    'photo_url' => $demande->user->assure->photo_url,
                ] : null;
            } elseif ($demande->type_demandeur === TypeDemandeurEnum::ENTREPRISE->value) {
                $clientData['entreprise'] = $demande->user->entreprise ? [
                    'id' => $demande->user->entreprise->id,
                    'raison_sociale' => $demande->user->entreprise->raison_sociale,
                    'nombre_employe' => $demande->user->entreprise->nombre_employe,
                    'secteur_activite' => $demande->user->entreprise->secteur_activite,
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

            $perPage = $request->input('per_page', 20);

            // Récupérer les propositions de contrat acceptées
            $query = PropositionContrat::with([
                'demandeAdhesion.user.assure',
                'contrat'
            ])
                ->where('statut', StatutPropositionContratEnum::ACCEPTEE->value)
                ->whereHas('demandeAdhesion', function ($q) {
                    $q->whereIn('type_demandeur', [
                        TypeDemandeurEnum::PHYSIQUE->value,
                        TypeDemandeurEnum::ENTREPRISE->value
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

            $propositions = $query->paginate($perPage);

            $clients = $propositions->getCollection()->map(function ($proposition) {
                $demande = $proposition->demandeAdhesion;
                $user = $demande->user;

                // Vérifier si des prestataires sont déjà assignés
                $prestatairesAssignes = ClientContrat::where('user_id', $user->id)
                    ->where('contrat_id', $proposition->contrat->id)
                    ->whereHas('prestataires', function ($q) {
                        $q->where('statut', 'ACTIF');
                    })
                    ->exists();

                return [
                    'id' => $user->id,
                    'nom' => $user->assure->nom,
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

            return ApiResponse::success([
                'data' => $clients,
                'pagination' => [
                    'current_page' => $propositions->currentPage(),
                    'per_page' => $propositions->perPage(),
                    'total' => $propositions->total(),
                    'last_page' => $propositions->lastPage(),
                ]
            ], 'Clients avec contrats acceptés récupérés avec succès');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des clients avec contrats acceptés', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return ApiResponse::error('Erreur lors de la récupération: ' . $e->getMessage(), 500);
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

            $perPage = $request->input('per_page', 50);

            $query = Prestataire::with('user')
                ->where('statut', \App\Enums\StatutPrestataireEnum::ACTIF);

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

            $prestataires = $query->paginate($perPage);

            $prestatairesList = $prestataires->getCollection()->map(function ($prestataire) {
                return [
                    'id' => $prestataire->id,
                    'raison_sociale' => $prestataire->raison_sociale,
                    'type_prestataire' => $prestataire->type_prestataire,
                    'adresse' => $prestataire->user->adresse,
                    'contact' => $prestataire->user->contact ?? null,
                    'email' => $prestataire->user->email,
                    'statut' => $prestataire->statut,
                    'nombre_clients_assignes' => ClientPrestataire::where('prestataire_id', $prestataire->id)
                        ->where('statut', 'actif')
                        ->count(),
                    'created_at' => $prestataire->created_at,
                ];
            });

            return ApiResponse::success([
                'data' => $prestatairesList,
                'pagination' => [
                    'current_page' => $prestataires->currentPage(),
                    'per_page' => $prestataires->perPage(),
                    'total' => $prestataires->total(),
                    'last_page' => $prestataires->lastPage(),
                ]
            ], 'Prestataires pour assignation récupérés avec succès');
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

            $client = User::find($clientId);
            if (!$client) {
                return ApiResponse::error('Client non trouvé', 404);
            }

            $clientContrat = ClientContrat::where('user_id', $clientId)->where('statut', 'actif')->first();

            $clientPrestataire = ClientPrestataire::where('client_contrat_id', $clientContrat->id)
                ->with(['clientContrat', 'prestataire'])
                ->where('statut', StatutPrestataireEnum::ACTIF->value)
                ->get();


            return ApiResponse::success([
                'client' => [
                    'id' => $client->id,
                    'nom' => $client->assure->nom,
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
}
