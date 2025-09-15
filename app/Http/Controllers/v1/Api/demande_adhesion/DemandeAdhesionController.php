<?php

namespace App\Http\Controllers\v1\Api\demande_adhesion;

use App\Enums\EmailType;
use App\Enums\TypeDemandeurEnum;
use App\Enums\TypeDonneeEnum;
use App\Enums\TypePrestataireEnum;
use App\Enums\StatutDemandeAdhesionEnum;
use App\Enums\LienParenteEnum;
use App\Enums\RoleEnum;
use App\Enums\StatutAssureEnum;
use App\Enums\StatutClientEnum;
use App\Enums\StatutContratEnum;
use App\Enums\StatutPrestataireEnum;
use App\Enums\StatutPropositionContratEnum;
use App\Enums\TypeClientEnum;
use App\Helpers\ApiResponse;
use App\Helpers\ImageUploadHelper;
use App\Helpers\PdfUploadHelper;
use App\Helpers\PrestataireDocumentHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\DemandeAdhesionClientFormRequest;
use App\Http\Requests\DemandeAdhesionEntrepriseFormRequest;
use App\Http\Requests\DemandeAdhesionPrestataireFormRequest;
use App\Http\Requests\DemandeAdhesionRejectFormRequest;
use App\Http\Requests\demande_adhesion\StoreDemandeAdhesionRequest;
use App\Http\Requests\ValiderProspectDemande;
use App\Http\Requests\demande_adhesion\SoumissionEmployeFormRequest;
use App\Http\Requests\demande_adhesion\StoreDemandeAdhesionPhysiqueRequest;
use App\Http\Requests\demande_adhesion\StoreDemandeAdhesionPrestataireRequest;
use App\Http\Requests\demande_adhesion\ValiderProspectRequest;
use App\Http\Requests\demande_adhesion\ValiderPrestataireRequest;
use App\Http\Resources\DemandeAdhesionResource;
use App\Http\Resources\DemandeAdhesionEntrepriseResource;
use App\Http\Resources\DemandeAdhesionPrestataireResource;
use App\Http\Resources\PropositionContratResource;
use App\Http\Resources\QuestionResource;
use App\Jobs\SendEmailJob;
use App\Models\Assure;
use App\Models\Client;
use App\Models\ClientContrat;
use App\Models\ClientPrestataire;
use App\Models\TypeContrat;
use App\Models\DemandeAdhesion;
use App\Models\InvitationEmploye;
use App\Models\Personnel;
use App\Models\Prestataire;
use App\Models\Question;
use App\Models\ReponseQuestionnaire;
use App\Models\User;
use App\Models\Entreprise;
use App\Models\PropositionContrat;
use App\Services\DemandeValidatorService;
use App\Services\DemandeReponseValidatorService;
use App\Services\NotificationService;
use App\Services\DemandeAdhesionStatsService;
use App\Services\DemandeAdhesionService;
use App\Traits\DemandeAdhesionDataTrait;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DemandeAdhesionController extends Controller
{
    use DemandeAdhesionDataTrait;

    protected NotificationService $notificationService;
    protected DemandeValidatorService $demandeValidatorService;
    protected DemandeAdhesionStatsService $statsService;
    protected DemandeAdhesionService $demandeAdhesionService;

    public function __construct(
        NotificationService $notificationService, 
        DemandeValidatorService $demandeValidatorService,
        DemandeAdhesionStatsService $statsService,
        DemandeAdhesionService $demandeAdhesionService
    ) {
        $this->notificationService = $notificationService;
        $this->demandeValidatorService = $demandeValidatorService;
        $this->statsService = $statsService;
        $this->demandeAdhesionService = $demandeAdhesionService;
    }


    public function index(Request $request)
    {
        $user = Auth::user();
        $query = DemandeAdhesion::with([
            'user', // Charger l'utilisateur
            'user.entreprise', // Charger les données entreprise si existantes
            'user.prestataire', // Charger les données prestataire si existantes
            'user.assure',
            'reponsesQuestionnaire.question' // Charger les réponses avec leurs questions
        ]);

        // Appliquer les filtres via le service
        // $this->demandeAdhesionService->applyRoleFilters($query, $user);
        // $this->demandeAdhesionService->applyStatusFilters($query, $request);
        // $this->demandeAdhesionService->applySearchFilter($query, $request);
        
        // Pagination
        // $perPage = $request->query('per_page', 10);
        $demandes = $query->orderByDesc('created_at')->get();

       
        return ApiResponse::success(DemandeAdhesionResource::collection($demandes), 'Liste des demandes d\'adhésion récupérée avec succès', 200);
    }

    public function hasDemande()
    {
        $user = Auth::user();
        
        // Récupérer la demande via la relation client
        $demande = DemandeAdhesion::whereHas('client', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->orderBy('created_at', 'desc')
        ->first();

        if (!$demande) {
            return ApiResponse::success([
                'existing' => false,
                'demande' => null,
                'can_submit' => true,
                'status' => 'none'
            ], 'Aucune demande d\'adhésion trouvée');
        }

        $status = $demande->statut?->value ?? $demande->statut;
        $canSubmit = in_array($status, ['rejetee']); // Can resubmit if rejected

        return ApiResponse::success([
            'existing' => true,
            'demande' => $demande,
            'can_submit' => $canSubmit,
            'status' => $status,
            'motif_rejet' => $demande->motif_rejet ?? null,
            'valide_par' => $demande->validePar ?? null,
            'valider_a' => $demande->valider_a ?? null
        ], 'Demande d\'adhésion récupérée avec succès');
    }


     /**
     * Soumission d'une demande d'adhésion pour une personne physique
     */
    public function store(StoreDemandeAdhesionRequest $request)
    {
        $user = Auth::user();
        $data = $request->validated();
        $typeDemandeur = $data['type_demandeur'];


        Log::info('Demande d\'adhésion soumise', ['data' => $data]);

        // Vérifier si l'utilisateur a déjà une demande en cours ou validée (optionnel)
        if ($this->demandeValidatorService->hasPendingDemande($data)) {
            return ApiResponse::error('Vous avez déjà une demande d\'adhésion en cours de traitement. Veuillez attendre la réponse.', 400);
        }
        if ($this->demandeValidatorService->hasValidatedDemande($data)) {
            return ApiResponse::error('Vous avez déjà une demande d\'adhésion validée. Vous ne pouvez plus soumettre une nouvelle demande.', 400);
        }

        DB::beginTransaction();
        try {
           if($typeDemandeur === TypeDemandeurEnum::PHYSIQUE->value || $typeDemandeur === TypeDemandeurEnum::ENTREPRISE->value){
            $demande = $this->demandeValidatorService->createDemandeAdhesionPhysique($data, $user);
           }else{
            $demande = $this->demandeValidatorService->createDemandeAdhesionPrestataire($data, $user);
           }

            // Notifier selon le type de demandeur via le service
            $this->demandeAdhesionService->notifyByDemandeurType($demande, $typeDemandeur);

            DB::commit();
            return ApiResponse::success(null, 'Demande d\'adhésion soumise avec succès.', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Erreur lors de la soumission de la demande d\'adhésion : ' . $e->getMessage(), 500);
        }
    }
  

    public function show($id)
    {
        Log::info('show demande', ['id' => $id]);
        $user = Auth::user();
        

        $demande = DemandeAdhesion::with([
            'user',
            'user.assure',
            'propositionsContrat.contrat',
            'user.assure.beneficiaires',
            'user.assure.beneficiaires.reponsesQuestionnaire.question',
            'user.entreprise',
            'user.prestataire',
            'reponsesQuestionnaire'=>function($query) use ($id){
                $query->where('demande_adhesion_id', $id);
            },
            'assures.beneficiaires',
            'assures.reponsesQuestionnaire.question',
        ])->find($id);

        if (!$demande) {
            return ApiResponse::error('Demande d\'adhésion non trouvée', 404);
        }
        // Vérification des permissions selon le rôle
        if ($user->hasRole('technicien')) {
            // Techniciens : seulement physique et entreprise
            if (!in_array($demande->type_demandeur->value, [
                TypeDemandeurEnum::PHYSIQUE->value,
                TypeDemandeurEnum::ENTREPRISE->value
            ])) {
                return ApiResponse::error('Vous n\'avez pas les permissions pour consulter cette demande d\'adhésion.', 403);
            }
        } else if ($user->hasRole('medecin_controleur')) {
            // Médecins contrôleurs : seulement prestataires
            if (!in_array($demande->type_demandeur->value, TypePrestataireEnum::values())) {
                return ApiResponse::error('Vous n\'avez pas les permissionrzas pour consulter cette demande d\'adhésion.', 403);
            }
        }
        // Admin global : accès à toutes les demandes (pas de vérification)

        $response = [
            'id' => $demande->id,
            'type_demandeur' => $demande->type_demandeur?->value ?? $demande->type_demandeur,
            'statut' => $demande->statut?->value ?? $demande->statut,
            'created_at' => $demande->created_at,
            'updated_at' => $demande->updated_at,
            'motif_rejet' => $demande->motif_rejet,
            'valide_par' => $demande->validePar ? [
                'id' => $demande->validePar->id,
                'nom' => $demande->validePar->nom,
                'prenoms' => $demande->validePar->prenoms,
            ] : null,
            'valider_a' => $demande->valider_a,
        ];

        // Selon le type de demandeur
        if ($demande->type_demandeur->value === TypeDemandeurEnum::PHYSIQUE->value) {
            $response = array_merge($response, $this->statsService->getPhysiqueData($demande));
        } elseif ($demande->type_demandeur->value === TypeDemandeurEnum::ENTREPRISE->value) {
            $response = array_merge($response, $this->statsService->getEntrepriseData($demande));
        } else {
            // Tous les autres types sont des prestataires
            $response = array_merge($response, $this->statsService->getPrestataireData($demande));
        }

        return ApiResponse::success($response, 'Détails de la demande d\'adhésion');
    }


    /**
     * Rejeter une demande d'adhésion (réservé au personnel)
     */
    public function rejeter(DemandeAdhesionRejectFormRequest $request, int $id)
    {
        // Récupérer le personnel connecté
        $personnel = Auth::user()->personnel;

        // Validation des données
        $validatedData = $request->validated();

        $demande = DemandeAdhesion::find($id);

        if (!$demande) {
            return ApiResponse::error('Demande d\'adhésion non trouvée', 404);
        }

        if (!$demande->isPending()) {
            return ApiResponse::error('Seules les demandes en attente peuvent être rejetées', 400);
        }

        try {
            // Rejeter la demande via le service
            $demande = $this->demandeAdhesionService->rejeterDemande($demande, $personnel, $validatedData['motif_rejet']);

            $notificationService = app(NotificationService::class);
            $notificationService->createNotification(
                $demande->user->id,
                'Demande d\'adhésion rejetée',
                "Votre demande d'adhésion a été rejetée. Consultez votre email pour plus de détails.",
                'demande_rejetee',
            );
            
            // Envoyer l'email
            $this->notificationService->sendEmail($demande->user->email, 'Demande d\'adhésion rejetée', EmailType::REJETEE->value, [
                'demande' => $demande,
            ]);
            
            return ApiResponse::success([
                'demande_id' => $demande->id,
                'statut' => $demande->statut?->value ?? $demande->statut,
                'rejetee_par' => $personnel->nom . ' ' . ($personnel->prenoms ?? '')
            ], 'Demande d\'adhésion rejetée avec succès');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }




    /**
     * Consulter une demande d'adhésion spécifique d'un employé
     */
    public function demandeEmploye(int $id)
    {
        $user = Auth::user();

        // Vérifier que l'utilisateur est une entreprise
        if (!$user->hasRole('entreprise') || !$user->entreprise) {
            return ApiResponse::error('Seules les entreprises peuvent consulter les demandes de leurs employés.', 403);
        }

        $entreprise = $user->entreprise;

        $demande = DemandeAdhesion::with([
            'user',
            'reponsesQuestionnaire.question',
            'assures' => function ($query) use ($entreprise) {
                $query->where('entreprise_id', $entreprise->id);
            },
            'assures.reponsesQuestionnaire.question',
            'validePar'
        ])
            ->where('id', $id)
            ->where('type_demandeur', TypeDemandeurEnum::PHYSIQUE->value)
            ->whereHas('assures', function ($query) use ($entreprise) {
                $query->where('entreprise_id', $entreprise->id);
            })
            ->first();

        if (!$demande) {
            return ApiResponse::error('Demande d\'adhésion non trouvée ou accès non autorisé.', 404);
        }

        // Récupérer l'employé de cette demande qui appartient à l'entreprise
        $employe = $demande->assures->where('entreprise_id', $entreprise->id)->first();

        $demandeTransformee = [
            'id' => $demande->id,
            'statut' => $demande->statut?->value ?? $demande->statut,
            'type_demandeur' => $demande->type_demandeur?->value ?? $demande->type_demandeur,
            'created_at' => $demande->created_at,
            'updated_at' => $demande->updated_at,
            'valide_par' => $demande->validePar ? [
                'id' => $demande->validePar->id,
                'nom' => $demande->validePar->nom,
                'prenoms' => $demande->validePar->prenoms,
                'email' => $demande->validePar->email
            ] : null,
            'valider_a' => $demande->valider_a,
            'motif_rejet' => $demande->motif_rejet,
            'commentaires_technicien' => $demande->commentaires_technicien,

            // Informations sur l'employé
            'employe' => $employe ? [
                'id' => $employe->id,
                'nom' => $employe->nom,
                'prenoms' => $employe->prenoms,
                'email' => $employe->email,
                'contact' => $employe->contact,
                'profession' => $employe->profession,
                'date_naissance' => $employe->date_naissance,
                'sexe' => $employe->sexe,
                'statut' => $employe->statut,
                'lien_parente' => $employe->lien_parente,
                'photo_url' => $employe->photo_url,
                'reponses_questionnaire' => $employe->reponsesQuestionnaire->map(function ($reponse) {
                    return [
                        'question_id' => $reponse->question_id,
                        'question_libelle' => $reponse->question->libelle,
                        'type_donnee' => $reponse->question->type_donnee,
                        'reponse_text' => $reponse->reponse_text,
                        'reponse_number' => $reponse->reponse_number,
                        'reponse_bool' => $reponse->reponse_bool,
                        'reponse_date' => $reponse->reponse_date,
                        'reponse_fichier' => $reponse->reponse_fichier,
                    ];
                })
            ] : null,

            // Réponses du questionnaire principal
            'reponses_questionnaire' => $demande->reponsesQuestionnaire->map(function ($reponse) {
                return [
                    'question_id' => $reponse->question_id,
                    'question_libelle' => $reponse->question->libelle,
                    'type_donnee' => $reponse->question->type_donnee,
                    'reponse_text' => $reponse->reponse_text,
                    'reponse_number' => $reponse->reponse_number,
                    'reponse_bool' => $reponse->reponse_bool,
                    'reponse_date' => $reponse->reponse_date,
                    'reponse_fichier' => $reponse->reponse_fichier,
                ];
            }),

            // Informations sur l'utilisateur qui a soumis la demande
            'demandeur' => [
                'id' => $demande->user->id,
                'email' => $demande->user->email,
                'contact' => $demande->user->contact,
                'adresse' => $demande->user->adresse,
            ],

            // Statistiques
            'statistiques' => [
                'total_reponses' => $demande->reponsesQuestionnaire->count(),
                'reponses_employe' => $employe ? $employe->reponsesQuestionnaire->count() : 0,
                'reponses_demande' => $demande->reponsesQuestionnaire->count(),
            ]
        ];

        return ApiResponse::success($demandeTransformee, 'Détails de la demande d\'adhésion de l\'employé récupérés avec succès.');
    }

  

    /**
     * Statistiques des demandes d'adhésion
     */
    public function stats()
    {
        return $this->demandeAdhesionService->getStats(Auth::user());
    }

    /**
     * Accepter une proposition de contrat
     */
    public function accepterContrat($propositionId)
    {

        try {
            $user = Auth::user();
            
            // Récupérer la proposition
            $proposition = PropositionContrat::with([
                'demandeAdhesion.user',
                'contrat',
                'technicien'
            ])->findOrFail($propositionId);

            // Vérifier que la proposition appartient à l'utilisateur connecté
            if ($proposition->demandeAdhesion->user_id !== $user->id) {
                return ApiResponse::error('Accès non autorisé', 403);
            }

            // Vérifier que la proposition est en statut PROPOSEE   
            if ($proposition->statut->value !== StatutPropositionContratEnum::PROPOSEE->value) {
                return ApiResponse::error('Cette proposition a déjà été traitée', 400);
            }

            DB::beginTransaction();

            try {
                // 1. Créer l'entrée dans client_contrats (pivot table)
                $clientContrat = ClientContrat::create([
                    'user_id' => $proposition->demandeAdhesion->user_id,
                    'contrat_id' => $proposition->contrat->id,
                    'type_client' => $proposition->demandeAdhesion->type_demandeur,
                    'date_debut' => now(),
                    'date_fin' => now()->addYear(),
                    'numero_police' => ClientContrat::generateNumeroPolice(),
                    'statut' => StatutContratEnum::ACTIF->value,
                ]);

                $proposition->demandeAdhesion->user->update([
                    'solde' => $proposition->contrat->prime_totale
                ]);

                // 2. Mettre à jour la proposition
                $proposition->update([
                    'statut' => StatutPropositionContratEnum::ACCEPTEE->value,
                    'date_acceptation' => now()
                ]);

                // 3. Mettre à jour la demande d'adhésion
                $proposition->demandeAdhesion->update([
                    'statut' => StatutDemandeAdhesionEnum::ACCEPTEE->value,
                    'contrat_id' => $clientContrat->id
                ]);

                $nom = $proposition->demandeAdhesion->user->assure->nom ?? $proposition->demandeAdhesion->user->entreprise->raison_sociale;
                // 4. Notification au technicien
                $this->notificationService->createNotification(
                    $proposition->technicien->user_id,
                    'TypeContrat accepté par le client',
                    "Le client {$nom} a accepté votre proposition de contrat.",
                    'contrat_accepte_technicien', 
                    [
                        'client_nom' => $proposition->demandeAdhesion->user->email,
                        'contrat_nom' => $proposition->contrat->libelle,
                        'type' => 'contrat_accepte_technicien'
                    ]
                );

                // 5. Notification au client
                $this->notificationService->createNotification(
                    $user->id,
                    'TypeContrat accepté avec succès',
                    "Votre contrat d'assurance est maintenant actif.",
                    'contrat_accepte',
                    [
                        'contrat_id' => $clientContrat->id,
                        'date_debut' => $clientContrat->date_debut,
                        'type' => 'contrat_accepte'
                    ]
                );

                DB::commit();

                return ApiResponse::success([
                    'contrat_id' => $clientContrat->id,
                    'message' => 'TypeContrat accepté avec succès'
                ], 'TypeContrat accepté avec succès');

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'acceptation du contrat', [
                'error' => $e->getMessage(),
                'proposition_id' => $propositionId,
                'user_id' => Auth::id()
            ]);

            return ApiResponse::error('Erreur lors de l\'acceptation du contrat', 500, $e->getMessage());
        }
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
     * Récupérer les propositions de contrat d'une demande d'adhésion
     */
    public function getPropositionsContrat(int $id)
    {
        $demande = DemandeAdhesion::find($id);
        
        if (!$demande) {
            return ApiResponse::error('Demande d\'adhésion non trouvée', 404);
        }

        $propositions = $demande->propositionsContrat()
            ->with(['contrat', 'technicien', 'garanties'])
            ->orderBy('created_at', 'desc')
            ->get();

        return ApiResponse::success(
            PropositionContratResource::collection($propositions),
            'Propositions de contrat récupérées avec succès'
        );
    }

    /**
     * Récupérer une proposition de contrat spécifique
     */
    public function getPropositionContrat(int $demandeId, int $propositionId)
    {
        $demande = DemandeAdhesion::find($demandeId);
        
        if (!$demande) {
            return ApiResponse::error('Demande d\'adhésion non trouvée', 404);
        }

        $proposition = $demande->propositionsContrat()
            ->with(['contrat', 'technicien', 'categoriesGaranties.garanties', 'demandeAdhesion.user'])
            ->find($propositionId);

        if (!$proposition) {
            return ApiResponse::error('Proposition de contrat non trouvée', 404);
        }

        return ApiResponse::success(
            new PropositionContratResource($proposition),
            'Proposition de contrat récupérée avec succès'
        );
    }
}
