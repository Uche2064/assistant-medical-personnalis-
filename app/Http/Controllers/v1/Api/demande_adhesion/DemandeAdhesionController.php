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
use App\Http\Requests\demande_adhesion\ProposerContratRequest;
use App\Http\Resources\DemandeAdhesionResource;
use App\Http\Resources\DemandeAdhesionEntrepriseResource;
use App\Http\Resources\DemandeAdhesionPrestataireResource;
use App\Http\Resources\QuestionResource;
use App\Jobs\SendEmailJob;
use App\Models\Assure;
use App\Models\Client;
use App\Models\ClientContrat;
use App\Models\Contrat;
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

    public function __construct(NotificationService $notificationService, DemandeValidatorService $demandeValidatorService)
    {
        $this->notificationService = $notificationService;
        $this->demandeValidatorService = $demandeValidatorService;
    }


    public function index(Request $request)
    {
        $user = Auth::user();
        $query = DemandeAdhesion::with([
            'user', // Charger l'utilisateur
            'user.client', // Charger les données client si existantes
            'user.entreprise', // Charger les données entreprise si existantes
            'user.prestataire', // Charger les données prestataire si existantes
            'reponsesQuestionnaire.question' // Charger les réponses avec leurs questions
        ]);

        // Filtrage basé sur le rôle de l'utilisateur
        if ($user->hasRole('technicien')) {
            $query->whereIn('type_demandeur', [TypeDemandeurEnum::PHYSIQUE->value, TypeDemandeurEnum::ENTREPRISE->value]);
        } elseif ($user->hasRole('medecin_controleur')) {
            $query->whereIn('type_demandeur', [
                TypeDemandeurEnum::CENTRE_DE_SOINS->value,
                TypeDemandeurEnum::LABORATOIRE_CENTRE_DIAGNOSTIC->value,
                TypeDemandeurEnum::PHARMACIE->value,
                TypeDemandeurEnum::OPTIQUE->value,
            ]);
        }

        // Filtrage par statut si fourni
        $status = $request->input('statut');
        if ($status) {
            $query->where('statut', match ($status) {
                'en_attente' => StatutDemandeAdhesionEnum::EN_ATTENTE->value,
                'validee'    => StatutDemandeAdhesionEnum::VALIDEE->value,
                'rejetee'    => StatutDemandeAdhesionEnum::REJETEE->value,
                default      => null
            });
        }

        // Pagination
        $perPage = $request->query('per_page', 10);
        $demandes = $query->orderByDesc('created_at')->paginate($perPage);

        $paginatedData = new LengthAwarePaginator(
            DemandeAdhesionResource::collection($demandes),
            $demandes->total(),
            $demandes->perPage(),
            $demandes->currentPage(),
            ['path' => Paginator::resolveCurrentPath()]
        );

        return ApiResponse::success($paginatedData, 'Liste des demandes d\'adhésion récupérée avec succès', 200);
    }

    public function hasDemande()
    {
        $demande = DemandeAdhesion::with('reponsesQuestionnaire')
            ->where('user_id', Auth::user()->id)
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

        $status = $demande->statut->value;
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
            $assurePrincipal = Assure::where('user_id', $user->id)->first();
            // Créer la demande d'adhésion
            $demande = DemandeAdhesion::create([
                'type_demandeur' => $typeDemandeur,
                'statut' => StatutDemandeAdhesionEnum::EN_ATTENTE->value,
                'user_id' => $user->id,
            ]);
            $assurePrincipal->update([
                'demande_adhesion_id' => $demande->id,
            ]);
            // Enregistrer les réponses au questionnaire principal
            foreach ($data['reponses'] as $reponse) {
                $this->enregistrerReponsePersonne($typeDemandeur, $demande->user_id, $reponse, $demande->id);
            }
            // Enregistrer les bénéficiaires si fournis
            if (!empty($data['beneficiaires'])) {
                foreach ($data['beneficiaires'] as $beneficiaire) {
                    $this->enregistrerBeneficiaire($demande, $beneficiaire, $assurePrincipal);
                }
            }
            DB::commit();
            return ApiResponse::success(null, 'Demande d\'adhésion soumise avec succès.', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Erreur lors de la soumission de la demande d\'adhésion : ' . $e->getMessage(), 500);
        }
    }

    public function show(int $id)
    {
        Log::info('show demande', ['id' => $id]);

        $demande = $this->loadDemandeWithRelations($id);

        if ($demande == null) {
            return ApiResponse::error('Demande d\'adhésion non trouvée', 404);
        }

        $demande = $this->loadDemandeWithRelations($id);

        return ApiResponse::success($demande, 'Détails de la demande d\'adhésion');
    }





    public function download($id)
    {
        $demande = DemandeAdhesion::with([
            'user',
            'user.entreprise',
            'user.prestataire',
            'user.client',
            'validePar', // validePar est déjà un Personnel
            'reponsesQuestionnaire' => function ($query) use ($id) {
                $query->where('demande_adhesion_id', $id);
            },
            'assures.reponsesQuestionnaire.question',
            'employes.reponsesQuestionnaire.question',
            'beneficiaires.reponsesQuestionnaire.question'
        ])->find($id);

        if (!$demande) {
            return ApiResponse::error('Demande d\'adhésion non trouvée', 404);
        }

        // Préparer les données pour le PDF
        $data = [
            'demande' => $demande,
            'baseUrl' => url('/'), // URL de base pour les fichiers
        ];

        // Générez le PDF
        $pdf = Pdf::loadView('pdf.demande-adhesion', $data);

        // Retournez le PDF en téléchargement
        return $pdf->download("demande-adhesion-{$id}.pdf");
    }



    /**
     * Proposer un contrat à un prospect (client physique ou entreprise) par un technicien
     */
    public function proposerContrat(ProposerContratRequest $request, int $id)
    {
        try {
            $validatedData = $request->validated();
            $technicien = Auth::user();
            $demande = DemandeAdhesion::with(['user'])->find($id);

            if (!$demande) {
                return ApiResponse::error('Demande d\'adhésion non trouvée', 404);
            }

            // Vérifier que la demande est en attente
            if (!$demande->isPending()) {
                return ApiResponse::error('Cette demande a déjà été traitée', 400);
            }

            // Vérifier que le contrat existe et est actif
            $contrat = Contrat::with(['garanties'])->find($validatedData['contrat_id']);
            if (!$contrat || !$contrat->est_actif) {
                return ApiResponse::error('Contrat non valide ou introuvable', 400);
            }

            DB::beginTransaction();

            // Créer la proposition de contrat
            $propositionContrat = PropositionContrat::create([
                'demande_adhesion_id' => $demande->id,
                'contrat_id' => $contrat->id,
                'prime_proposee' => $validatedData['prime_proposee'],
                'taux_couverture' => $validatedData['taux_couverture'] ?? 80,
                'frais_gestion' => $validatedData['frais_gestion'] ?? 20,
                'commentaires_technicien' => $validatedData['commentaires'],
                'technicien_id' => $technicien->personnel->id,
                'statut' => StatutPropositionContratEnum::PROPOSEE->value,
                'date_proposition' => now(),
            ]);

            // Associer les garanties si fournies
            if (!empty($validatedData['garanties_incluses'])) {
                foreach ($validatedData['garanties_incluses'] as $garantieId) {
                    $propositionContrat->garanties()->attach($garantieId);
                }
            } else {
                // Associer toutes les garanties du contrat par défaut
                foreach ($contrat->garanties as $garantie) {
                    $propositionContrat->garanties()->attach($garantie->id);
                }
            }

            // Générer un token pour l'acceptation du contrat
            $token = Str::random(60);
            $tokenExpiration = now()->addDays(7); // Lien valable 7 jours

            // Stocker le token
            Cache::put("proposition_contrat_{$propositionContrat->id}", [
                'proposition_id' => $propositionContrat->id,
                'demande_id' => $demande->id,
                'user_id' => $demande->demandeur->id,
                'expires_at' => $tokenExpiration,
            ], $tokenExpiration);

            // Notifier le prospect via l'application
            $this->notificationService->createNotification(
                $demande->demandeur->id,
                'Proposition de contrat reçue',
                "Un technicien a analysé votre demande et vous propose un contrat d'assurance. Consultez votre email pour les détails.",
                'contrat_propose'
            );

            // Envoyer l'email avec le lien d'acceptation
            $acceptationUrl = config('app.frontend_url', 'http://localhost:3000') . "/contrat/accepter/" . $token;

            dispatch(new SendEmailJob(
                $demande->demandeur->email,
                'Votre proposition de contrat d\'assurance',
                EmailType::CONTRAT_PRET->value,
                [
                    'demande' => $demande,
                    'proposition' => $propositionContrat,
                    'contrat' => $contrat,
                    'acceptationUrl' => $acceptationUrl,
                    'technicien' => $technicien->personnel,
                ]
            ));

            DB::commit();

            Log::info('Proposition de contrat créée', [
                'demande_id' => $demande->id,
                'proposition_id' => $propositionContrat->id,
                'contrat_id' => $contrat->id,
                'technicien_id' => $technicien->id,
                'type_demandeur' => $demande->type_demandeur->value,
            ]);

            return ApiResponse::success([
                'proposition_id' => $propositionContrat->id,
                'contrat_id' => $contrat->id,
                'type_contrat' => $contrat->type_contrat,
                'prime_proposee' => $propositionContrat->prime_proposee,
                'token_acceptation' => $token,
                'expiration_token' => $tokenExpiration,
                'statut' => $propositionContrat->statut->value,
                'propose_par' => $technicien->personnel->nom . ' ' . ($technicien->personnel->prenoms ?? ''),
            ], 'Proposition de contrat envoyée avec succès. Le client doit maintenant accepter ou refuser.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la proposition de contrat', [
                'error' => $e->getMessage(),
                'demande_id' => $id,
                'technicien_id' => Auth::id(),
            ]);

            return ApiResponse::error('Erreur lors de la proposition de contrat: ' . $e->getMessage(), 500);
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

            // Valider la demande
            $demande->validate($medecinControleur->personnel->id);

            // Notifier le prestataire
            $this->notificationService->createNotification(
                $demande->user->id,
                'Demande d\'adhésion validée',
                "Votre demande d'adhésion en tant que prestataire de soins a été validée par notre médecin contrôleur.",
                'demande_validee'
            );


            dispatch(new SendEmailJob($demande->user->email, 'Demande d\'adhésion prestataire validée', EmailType::ACCEPTED->value, [
                'demande' => $demande,
                'medecin_controleur' => $medecinControleur->personnel,
            ]));

            DB::commit();

            return ApiResponse::success([
                'demande_id' => $demande,
                'statut' => $demande->statut->value,
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
            // Rejet de la demande
            $demande->reject($validatedData['motif_rejet'], $personnel->id);
            $this->notificationService->sendEmail($demande->user->email, 'Demande d\'adhésion rejetée', EmailType::REJETEE->value, [
                'demande' => $demande,
            ]);
            return ApiResponse::success([
                'demande_id' => $demande->id,
                'statut' => $demande->statut->value,
                'rejetee_par' => $personnel->user->nom . ' ' . ($personnel->user->prenoms ?? '')
            ], 'Demande d\'adhésion rejetée avec succès');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }


    private function isUploadedFile($value): bool
    {
        return is_object($value) && method_exists($value, 'getClientOriginalName');
    }




    /**
     * Récupérer les contrats disponibles pour proposition
     */
    public function getContratsDisponibles()
    {
        try {
            $contrats = Contrat::with(['garanties.categorieGarantie'])
                ->where('est_actif', true)
                ->get()
                ->map(function ($contrat) {
                    return [
                        'id' => $contrat->id,
                        'nom' => $contrat->nom,
                        'type_contrat' => $contrat->type_contrat,
                        'description' => $contrat->description,
                        'prime_de_base' => $contrat->prime_de_base,
                        'garanties' => $contrat->garanties->map(function ($garantie) {
                            return [
                                'id' => $garantie->id,
                                'nom' => $garantie->nom,
                                'description' => $garantie->description,
                                'taux_couverture' => $garantie->taux_couverture,
                                'categorie' => [
                                    'id' => $garantie->categorieGarantie->id,
                                    'nom' => $garantie->categorieGarantie->nom,
                                ],
                            ];
                        }),
                    ];
                });

            return ApiResponse::success($contrats, 'Contrats disponibles récupérés avec succès');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des contrats disponibles', [
                'error' => $e->getMessage(),
            ]);

            return ApiResponse::error('Erreur lors de la récupération des contrats: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Consulter les liens d'invitation existants pour une entreprise
     */
    public function consulterLiensInvitation(Request $request)
    {
        $user = Auth::user();

        // Vérifier que l'utilisateur est une entreprise
        if (!$user->hasRole('entreprise') || !$user->entreprise) {
            return ApiResponse::error('Seules les entreprises peuvent consulter leurs liens d\'invitation.', 403);
        }

        $entreprise = $user->entreprise;

        $invitations = InvitationEmploye::with(['assure'])
            ->where('entreprise_id', $entreprise->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $liens = $invitations->map(function ($invitation) {
            return [
                'invitation_id' => $invitation->id,
                'employe_id' => $invitation->assure_id,
                'nom' => $invitation->assure ? $invitation->assure->nom . ' ' . $invitation->assure->prenoms : 'N/A',
                'email' => $invitation->email,
                'lien' => config('app.frontend_url') . "/employes/formulaire/{$invitation->token}",
                'token' => $invitation->token,
                'statut' => $invitation->expire_at > now() ? 'actif' : 'expire',
                'envoye_le' => $invitation->created_at,
                'expire_le' => $invitation->expire_at
            ];
        });

        return ApiResponse::success([
            'entreprise_id' => $entreprise->id,
            'raison_sociale' => $entreprise->raison_sociale,
            'total_liens' => $liens->count(),
            'liens_actifs' => $liens->where('statut', 'actif')->count(),
            'liens_expires' => $liens->where('statut', 'expire')->count(),
            'liens' => $liens
        ], 'Liens d\'invitation récupérés avec succès.');
    }

    /**
     * Consulter les demandes d'adhésion d'une entreprise
     */
    public function mesDemandesAdhesion(Request $request)
    {
        $user = Auth::user();

        // Vérifier que l'utilisateur est une entreprise
        if (!$user->hasRole('entreprise') || !$user->entreprise) {
            return ApiResponse::error('Seules les entreprises peuvent consulter leurs demandes d\'adhésion.', 403);
        }

        $entreprise = $user->entreprise;

        $query = DemandeAdhesion::with([
            'user.entreprise',
            'reponsesQuestionnaire.question',
            'assures' => function ($query) use ($entreprise) {
                $query->where('entreprise_id', $entreprise->id);
            },
            'assures.reponsesQuestionnaire.question'
        ])
            ->where('user_id', $user->id)
            ->where('type_demandeur', TypeDemandeurEnum::ENTREPRISE->value);

        // Filtrage par statut si fourni
        $status = $request->input('statut');
        if ($status) {
            $query->where('statut', match ($status) {
                'en_attente' => StatutDemandeAdhesionEnum::EN_ATTENTE->value,
                'validee'    => StatutDemandeAdhesionEnum::VALIDEE->value,
                'rejetee'    => StatutDemandeAdhesionEnum::REJETEE->value,
                default      => null
            });
        }

        // Pagination
        $perPage = $request->query('per_page', 10);
        $demandes = $query->orderByDesc('created_at')->paginate($perPage);

        // Transformer les données pour l'entreprise
        $demandesTransformees = $demandes->map(function ($demande) {
            return [
                'id' => $demande->id,
                'statut' => $demande->statut->value,
                'type_demandeur' => $demande->type_demandeur->value,
                'created_at' => $demande->created_at,
                'updated_at' => $demande->updated_at,
                'valide_par' => $demande->validePar ? [
                    'id' => $demande->validePar->id,
                    'nom' => $demande->validePar->nom,
                    'prenoms' => $demande->validePar->prenoms
                ] : null,
                'valider_a' => $demande->valider_a,
                'motif_rejet' => $demande->motif_rejet,
                'commentaires_technicien' => $demande->commentaires_technicien,

                // Informations sur les employés
                'employes' => $demande->assures->map(function ($assure) {
                    return [
                        'id' => $assure->id,
                        'nom' => $assure->nom,
                        'prenoms' => $assure->prenoms,
                        'email' => $assure->email,
                        'contact' => $assure->contact,
                        'profession' => $assure->profession,
                        'date_naissance' => $assure->date_naissance,
                        'sexe' => $assure->sexe,
                        'statut' => $assure->statut,
                        'lien_parente' => $assure->lien_parente,
                        'photo_url' => $assure->photo_url,
                        'reponses_questionnaire' => $assure->reponsesQuestionnaire->map(function ($reponse) {
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
                }),

                // Réponses du questionnaire principal (entreprise)
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
                }),

                // Statistiques
                'statistiques' => [
                    'total_employes' => $demande->assures->count(),
                    'employes_avec_reponses' => $demande->assures->filter(function ($assure) {
                        return $assure->reponsesQuestionnaire->count() > 0;
                    })->count(),
                    'employes_sans_reponses' => $demande->assures->filter(function ($assure) {
                        return $assure->reponsesQuestionnaire->count() == 0;
                    })->count(),
                ]
            ];
        });

        $paginatedData = new LengthAwarePaginator(
            $demandesTransformees,
            $demandes->total(),
            $demandes->perPage(),
            $demandes->currentPage(),
            ['path' => Paginator::resolveCurrentPath()]
        );

        return ApiResponse::success([
            'entreprise' => [
                'id' => $entreprise->id,
                'raison_sociale' => $entreprise->raison_sociale,
                'nombre_employe' => $entreprise->nombre_employe,
                'secteur_activite' => $entreprise->secteur_activite
            ],
            'demandes' => $paginatedData,
            'statistiques_globales' => [
                'total_demandes' => $demandes->total(),
                'demandes_en_attente' => $demandes->where('statut', StatutDemandeAdhesionEnum::EN_ATTENTE->value)->count(),
                'demandes_validees' => $demandes->where('statut', StatutDemandeAdhesionEnum::VALIDEE->value)->count(),
                'demandes_rejetees' => $demandes->where('statut', StatutDemandeAdhesionEnum::REJETEE->value)->count(),
            ]
        ], 'Demandes d\'adhésion de l\'entreprise récupérées avec succès.');
    }

    /**
     * Consulter une demande d'adhésion spécifique pour une entreprise
     */
    public function maDemandeAdhesion(int $id)
    {
        $user = Auth::user();

        // Vérifier que l'utilisateur est une entreprise
        if (!$user->hasRole('entreprise') || !$user->entreprise) {
            return ApiResponse::error('Seules les entreprises peuvent consulter leurs demandes d\'adhésion.', 403);
        }

        $demande = DemandeAdhesion::with([
            'user.entreprise',
            'reponsesQuestionnaire.question',
            'assures' => function ($query) use ($user) {
                $query->where('entreprise_id', $user->entreprise->id);
            },
            'assures.reponsesQuestionnaire.question',
            'validePar'
        ])
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->where('type_demandeur', TypeDemandeurEnum::ENTREPRISE->value)
            ->first();

        if (!$demande) {
            return ApiResponse::error('Demande d\'adhésion non trouvée ou accès non autorisé.', 404);
        }

        $demandeTransformee = [
            'id' => $demande->id,
            'statut' => $demande->statut->value,
            'type_demandeur' => $demande->type_demandeur->value,
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

            // Informations sur les employés
            'employes' => $demande->assures->map(function ($assure) {
                return [
                    'id' => $assure->id,
                    'nom' => $assure->nom,
                    'prenoms' => $assure->prenoms,
                    'email' => $assure->email,
                    'contact' => $assure->contact,
                    'profession' => $assure->profession,
                    'date_naissance' => $assure->date_naissance,
                    'sexe' => $assure->sexe,
                    'statut' => $assure->statut,
                    'lien_parente' => $assure->lien_parente,
                    'photo_url' => $assure->photo_url,
                    'reponses_questionnaire' => $assure->reponsesQuestionnaire->map(function ($reponse) {
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
                ];
            }),

            // Réponses du questionnaire principal (entreprise)
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

            // Statistiques
            'statistiques' => [
                'total_employes' => $demande->assures->count(),
                'employes_avec_reponses' => $demande->assures->filter(function ($assure) {
                    return $assure->reponsesQuestionnaire->count() > 0;
                })->count(),
                'employes_sans_reponses' => $demande->assures->filter(function ($assure) {
                    return $assure->reponsesQuestionnaire->count() == 0;
                })->count(),
                'pourcentage_completion' => $demande->assures->count() > 0
                    ? round(($demande->assures->filter(function ($assure) {
                        return $assure->reponsesQuestionnaire->count() > 0;
                    })->count() / $demande->assures->count()) * 100, 2)
                    : 0
            ]
        ];

        return ApiResponse::success($demandeTransformee, 'Détails de la demande d\'adhésion récupérés avec succès.');
    }

    /**
     * Consulter les demandes d'adhésion soumises par les employés de l'entreprise
     */
    public function demandesEmployes(Request $request)
    {
        $user = Auth::user();

        // Vérifier que l'utilisateur est une entreprise
        if (!$user->hasRole('entreprise') || !$user->entreprise) {
            return ApiResponse::error('Seules les entreprises peuvent consulter les demandes de leurs employés.', 403);
        }

        $entreprise = $user->entreprise;

        $query = DemandeAdhesion::with([
            'user',
            'reponsesQuestionnaire.question',
            'validePar'
        ])
            ->whereHas('assures', function ($query) use ($entreprise) {
                $query->where('entreprise_id', $entreprise->id);
            })
            ->where('type_demandeur', TypeDemandeurEnum::PHYSIQUE->value);

        // Filtrage par statut si fourni
        $status = $request->input('statut');
        if ($status) {
            $query->where('statut', match ($status) {
                'en_attente' => StatutDemandeAdhesionEnum::EN_ATTENTE->value,
                'validee'    => StatutDemandeAdhesionEnum::VALIDEE->value,
                'rejetee'    => StatutDemandeAdhesionEnum::REJETEE->value,
                default      => null
            });
        }

        // Filtrage par employé si fourni
        $employeId = $request->input('employe_id');
        if ($employeId) {
            $query->whereHas('assures', function ($query) use ($employeId) {
                $query->where('id', $employeId);
            });
        }

        // Pagination
        $perPage = $request->query('per_page', 10);
        $demandes = $query->orderByDesc('created_at')->paginate($perPage);

        // Transformer les données
        $demandesTransformees = $demandes->map(function ($demande) use ($entreprise) {
            // Récupérer les employés de cette demande qui appartiennent à l'entreprise
            $employes = $demande->assures->where('entreprise_id', $entreprise->id);

            return [
                'id' => $demande->id,
                'statut' => $demande->statut->value,
                'type_demandeur' => $demande->type_demandeur->value,
                'created_at' => $demande->created_at,
                'updated_at' => $demande->updated_at,
                'valide_par' => $demande->validePar ? [
                    'id' => $demande->validePar->id,
                    'nom' => $demande->validePar->nom,
                    'prenoms' => $demande->validePar->prenoms
                ] : null,
                'valider_a' => $demande->valider_a,
                'motif_rejet' => $demande->motif_rejet,
                'commentaires_technicien' => $demande->commentaires_technicien,

                // Informations sur l'employé qui a soumis la demande
                'employe' => $employes->first() ? [
                    'id' => $employes->first()->id,
                    'nom' => $employes->first()->nom,
                    'prenoms' => $employes->first()->prenoms,
                    'email' => $employes->first()->email,
                    'contact' => $employes->first()->contact,
                    'profession' => $employes->first()->profession,
                    'date_naissance' => $employes->first()->date_naissance,
                    'sexe' => $employes->first()->sexe,
                    'statut' => $employes->first()->statut,
                    'lien_parente' => $employes->first()->lien_parente,
                    'photo_url' => $employes->first()->photo_url,
                ] : null,

                // Réponses du questionnaire
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
                ]
            ];
        });

        $paginatedData = new LengthAwarePaginator(
            $demandesTransformees,
            $demandes->total(),
            $demandes->perPage(),
            $demandes->currentPage(),
            ['path' => Paginator::resolveCurrentPath()]
        );

        return ApiResponse::success([
            'entreprise' => [
                'id' => $entreprise->id,
                'raison_sociale' => $entreprise->raison_sociale,
                'nombre_employe' => $entreprise->nombre_employe,
                'secteur_activite' => $entreprise->secteur_activite
            ],
            'demandes' => $paginatedData,
            'statistiques_globales' => [
                'total_demandes' => $demandes->total(),
                'demandes_en_attente' => $demandes->where('statut', StatutDemandeAdhesionEnum::EN_ATTENTE->value)->count(),
                'demandes_validees' => $demandes->where('statut', StatutDemandeAdhesionEnum::VALIDEE->value)->count(),
                'demandes_rejetees' => $demandes->where('statut', StatutDemandeAdhesionEnum::REJETEE->value)->count(),
            ]
        ], 'Demandes d\'adhésion des employés récupérées avec succès.');
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
            'statut' => $demande->statut->value,
            'type_demandeur' => $demande->type_demandeur->value,
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

    public function getInvitationLink(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasRole('entreprise')) {
            return ApiResponse::error('Seules les entreprises peuvent générer un lien d\'invitation.', 403);
        }
        $invitation = InvitationEmploye::where('entreprise_id', $user->entreprise->id)
            ->where('expire_at', '>', now())
            ->first();
        return ApiResponse::success([
            'invitation_id' => $invitation->id,
            'token' => $invitation->token,
            'url' => config('app.frontend_url') . "/employes/formulaire/{$invitation->token}",
            'expire_at' => $invitation->expire_at,
        ], 'Lien d\'invitation récupéré avec succès.');
    }

    /**
     * Générer un lien d'invitation unique pour qu'un employé remplisse sa fiche d'adhésion (un seul lien actif par entreprise)
     */
    public function genererLienInvitationEmploye(Request $request)
    {
        $user = Auth::user();
        // Vérifier que l'utilisateur est une entreprise
        if (!$user->hasRole('entreprise')) {
            return ApiResponse::error('Seules les entreprises peuvent générer un lien d\'invitation.', 403);
        }
        $entrepriseId = $user->entreprise->id;
        // Chercher un lien actif existant
        $invitation = InvitationEmploye::where('entreprise_id', $entrepriseId)
            ->where('expire_at', '>', now())
            ->first();
        if ($invitation) {
            $url = config('app.frontend_url') . "/employes/formulaire/{$invitation->token}";
            return ApiResponse::success([
                'invitation_id' => $invitation->id,
                'url' => $url,
                'token' => $invitation->token,
                'expire_at' => $invitation->expire_at,
            ], 'Lien d\'invitation déjà existant.');
        }
        // Sinon, générer un nouveau lien
        $token = Str::uuid()->toString();
        $invitation = InvitationEmploye::create([
            'entreprise_id' => $entrepriseId,
            'token' => $token,
            'expire_at' => now()->addDays(7),
        ]);
        $url = config('app.frontend_url') . "/employes/formulaire/{$token}";
        return ApiResponse::success([
            'invitation_id' => $invitation->id,
            'token' => $token,
            'url' => $url,
            'expire_at' => $invitation->expire_at,
        ], 'Nouveau lien d\'invitation généré avec succès.');
    }

    /**
     * Afficher le formulaire d'adhésion employé via le token d'invitation
     */
    public function showFormulaireEmploye($token)
    {
        $invitation = InvitationEmploye::where('token', $token)
            ->where('expire_at', '>', now())
            ->first();
        if (!$invitation) {
            return ApiResponse::error('Lien d\'invitation invalide ou expiré.', 404);
        }
        // Récupérer les questions actives pour le type PHYSIQUE
        $questions = Question::active()->byDestinataire(TypeDemandeurEnum::PHYSIQUE->value)->get();
        return ApiResponse::success([
            'entreprise' => $invitation->entreprise,
            'token' => $token,
            'questions' => QuestionResource::collection($questions),
            'beneficiaires',
            'nom',
            'prenoms',
            'email',
            'date_naissance',
            'sexe',
            'contact',
            'adresse',
            'photo'
        ], 'Formulaire employé prêt à être rempli.');
    }

    /**
     * Soumettre la fiche employé via le lien d'invitation
     */
    public function soumettreFicheEmploye(SoumissionEmployeFormRequest $request, $token)
    {
        $invitation = InvitationEmploye::where('token', $token)
            ->where('expire_at', '>', now())
            ->first();
        if (!$invitation) {
            return ApiResponse::error('Lien d\'invitation invalide ou expiré.', 404);
        }
        $data = $request->validated();
        DB::beginTransaction();
        try {
            
            // Créer l'assuré principal (employé)
            $assure = Assure::create([
                'user_id' => null,
                'entreprise_id' => $invitation->entreprise_id,
                'nom' => $data['nom'],
                'prenoms' => $data['prenoms'],
                'date_naissance' => $data['date_naissance'],
                'sexe' => $data['sexe'],
                'profession' => $data['profession'] ?? null,
                'photo' => isset($data['photo']) ? ImageUploadHelper::uploadImage($data['photo'], 'uploads/employes') : null,
            ]);
            // Enregistrer les réponses au questionnaire
            foreach ($data['reponses'] as $reponse) {
                $this->enregistrerReponsePersonne(Assure::class, $assure->id, $reponse, null);
            }
            // Enregistrer les bénéficiaires (optionnels)
            if (isset($data['beneficiaires']) && is_array($data['beneficiaires'])) {
                foreach ($data['beneficiaires'] as $beneficiaire) {
                    $this->enregistrerBeneficiaire($assure, $beneficiaire, $assure);
                }
            }
            // Notifier l'entreprise
            $entreprise = $invitation->entreprise;
            if ($entreprise && $entreprise->user && $entreprise->user->email) {
                $this->notificationService->sendEmail(
                    $entreprise->user->email,
                    'Nouvelle fiche employé soumise',
                    'emails.nouvelle_fiche_employe',
                    [
                        'assure' => $assure,
                        'entreprise' => $entreprise,
                    ]
                );
            }
            DB::commit();
            return ApiResponse::success(null, 'Fiche employé soumise avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Erreur lors de la soumission de la fiche employé : ' . $e->getMessage(), 500);
        }
    }

    /**
     * Soumission groupée de la demande d'adhésion entreprise
     */
    public function soumettreDemandeAdhesionEntreprise(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasRole('entreprise') || !$user->entreprise) {
            return ApiResponse::error('Seules les entreprises peuvent soumettre une demande groupée.', 403);
        }
        $entreprise = $user->entreprise;
        $employes = Assure::where('entreprise_id', $entreprise->id)->get();
        if ($employes->isEmpty()) {
            return ApiResponse::error('Aucun employé n\'a encore soumis sa fiche.', 422);
        }
        DB::beginTransaction();
        try {
            // Créer la demande d'adhésion entreprise
            $demande = DemandeAdhesion::create([
                'type_demandeur' => TypeDemandeurEnum::ENTREPRISE->value,
                'statut' => StatutDemandeAdhesionEnum::EN_ATTENTE->value,
                'user_id' => $user->id,
                'entreprise_id' => $entreprise->id,
            ]);
            // Associer les employés à la demande (si relation many-to-many, sinon ignorer)
            // $demande->employes()->sync($employes->pluck('id'));
            // Notifier SUNU (technicien)

            DB::commit();
            return ApiResponse::success(new DemandeAdhesionEntrepriseResource($demande), 'Demande d\'adhésion entreprise soumise avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Erreur lors de la soumission de la demande d\'adhésion entreprise : ' . $e->getMessage(), 500);
        }
    }


    /**
     * Enregistrer une réponse au questionnaire pour une personne (assuré principal ou bénéficiaire)
     */
    private function enregistrerReponsePersonne($personneType, $personneId, array $reponseData, $demandeId)
    {
        $question = Question::find($reponseData['question_id']);
        if (!$question) return;

        $data = [
            'question_id' => $question->id,
            'personne_type' => $personneType,
            'personne_id' => $personneId,
            'demande_adhesion_id' => $demandeId,
        ];

        switch ($question->type_donnee) {
            case TypeDonneeEnum::BOOLEAN:
                $data['reponse_bool'] = filter_var($reponseData['reponse_bool'] ?? false, FILTER_VALIDATE_BOOLEAN);
                break;
            case TypeDonneeEnum::NUMBER:
                $data['reponse_number'] = floatval($reponseData['reponse_number'] ?? 0);
                break;
            case TypeDonneeEnum::DATE:
                $data['reponse_date'] = $reponseData['reponse_date'] ?? null;
                break;
            case TypeDonneeEnum::FILE:
                if (isset($reponseData['reponse_fichier']) && $this->isUploadedFile($reponseData['reponse_fichier'])) {
                    $data['reponse_fichier'] = ImageUploadHelper::uploadImage($reponseData['reponse_fichier'], 'uploads/demandes_adhesion/' . $demandeId);
                }
                break;
            case TypeDonneeEnum::TEXT:
                $data['reponse_text'] = $reponseData['reponse_text'] ?? null;
                break;
            case TypeDonneeEnum::SELECT:
                $data['reponse_text'] = $reponseData['reponse_text'] ?? null;
                break;
            case TypeDonneeEnum::CHECKBOX:
                $data['reponse_text'] = $reponseData['reponse_text'] ?? null;
                break;
            case TypeDonneeEnum::RADIO:
            default:
                $data['reponse_text'] = $reponseData['reponse_text'] ?? null;
                break;
        }
        ReponseQuestionnaire::create($data);
    }

    
    /**
     * Enregistrer un bénéficiaire et ses réponses
     */
    private function enregistrerBeneficiaire(DemandeAdhesion $demande, array $beneficiaire, Assure $assurePrincipal): void
    {
        // Créer le bénéficiaire dans la table assures (PAS de compte utilisateur)
        $benefAssure = Assure::create([
            'user_id' => null, // ❌ Bénéficiaires n'ont PAS de compte
            'assure_principal_id' => $assurePrincipal->id,
            'nom' => $beneficiaire['nom'], // ✅ Informations personnelles
            'prenoms' => $beneficiaire['prenoms'], // ✅ Informations personnelles0
            'date_naissance' => $beneficiaire['date_naissance'], // ✅ Informations personnelles
            'sexe' => $beneficiaire['sexe'], // ✅ Informations personnelles
            'lien_parente' => $beneficiaire['lien_parente'],
            'est_principal' => false, // ✅ Bénéficiaire = pas principal
            'photo' => $beneficiaire['photo'] ?? null,
        ]);

        // Enregistrer les réponses du bénéficiaire
        foreach ($beneficiaire['reponses'] as $reponse) {
            $this->enregistrerReponsePersonne('beneficiaire', $benefAssure->id, $reponse, $demande->id);
        }
    }

    /**
     * Statistiques des demandes d'adhésion
     */
    public function stats()
    {
        $user = Auth::user();
        $query = DemandeAdhesion::query();

        // Filtrer selon le rôle de l'utilisateur
        if ($user->hasRole('technicien')) {
            // Techniciens : seulement physique et entreprise
            $query->whereIn('type_demandeur', [
                TypeDemandeurEnum::PHYSIQUE->value,
                TypeDemandeurEnum::ENTREPRISE->value
            ]);
        } elseif ($user->hasRole('medecin_controleur')) {
            // Médecins contrôleurs : seulement prestataires
            $query->whereIn('type_demandeur', TypePrestataireEnum::values());
        }
        // Admin global : toutes les demandes (pas de filtre)

        $stats = [
            'total' => $query->count(),

            'en_attente' => (clone $query)->where('statut', 'en_attente')->count(),

            'validees' => (clone $query)->where('statut', 'validee')->count(),

            'rejetees' => (clone $query)->where('statut', 'rejetee')->count(),

            'repartition_par_type_demandeur' => (clone $query)->selectRaw('type_demandeur, COUNT(*) as count')
                ->groupBy('type_demandeur')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->type_demandeur->value ?? 'Non spécifié' => $item->count];
                }),

            'repartition_par_statut' => (clone $query)->selectRaw('statut, COUNT(*) as count')
                ->groupBy('statut')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->statut->value ?? 'Non spécifié' => $item->count];
                }),

            'repartition_statut_par_type' => (clone $query)->selectRaw('type_demandeur, statut, COUNT(*) as count')
                ->groupBy('type_demandeur', 'statut')
                ->get()
                ->groupBy('type_demandeur')
                ->map(function ($group) {
                    return $group->mapWithKeys(function ($item) {
                        return [$item->statut->value ?? 'Non spécifié' => $item->count];
                    });
                }),

            'demandes_par_mois' => (clone $query)->selectRaw('MONTH(created_at) as mois, COUNT(*) as count')
                ->whereYear('created_at', date('Y'))
                ->groupBy('mois')
                ->orderBy('mois')
                ->get()
                ->mapWithKeys(function ($item) {
                    $mois = [
                        1 => 'Janvier',
                        2 => 'Février',
                        3 => 'Mars',
                        4 => 'Avril',
                        5 => 'Mai',
                        6 => 'Juin',
                        7 => 'Juillet',
                        8 => 'Août',
                        9 => 'Septembre',
                        10 => 'Octobre',
                        11 => 'Novembre',
                        12 => 'Décembre'
                    ];
                    return [$mois[$item->mois] ?? "Mois {$item->mois}" => $item->count];
                }),
        ];

        return ApiResponse::success($stats, 'Statistiques des demandes d\'adhésion récupérées avec succès');
    }
}
