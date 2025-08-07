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
use App\Models\ClientPrestataire;
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
        $this->demandeAdhesionService->applyRoleFilters($query, $user);
        $this->demandeAdhesionService->applyStatusFilters($query, $request);

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
            if (!in_array($demande->type_demandeur, [
                TypeDemandeurEnum::PHYSIQUE,
                TypeDemandeurEnum::ENTREPRISE
            ])) {
                return ApiResponse::error('Vous n\'avez pas les permissions pour consulter cette demande d\'adhésion.', 403);
            }
        } elseif ($user->hasRole('medecin_controleur')) {
            // Médecins contrôleurs : seulement prestataires
            if (!in_array($demande->type_demandeur, TypePrestataireEnum::values())) {
                return ApiResponse::error('Vous n\'avez pas les permissions pour consulter cette demande d\'adhésion.', 403);
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
        if ($demande->type_demandeur === TypeDemandeurEnum::PHYSIQUE) {
            $response = array_merge($response, $this->statsService->getPhysiqueData($demande));
        } elseif ($demande->type_demandeur === TypeDemandeurEnum::ENTREPRISE) {
            $response = array_merge($response, $this->statsService->getEntrepriseData($demande));
        } else {
            // Tous les autres types sont des prestataires
            $response = array_merge($response, $this->statsService->getPrestataireData($demande));
        }

        return ApiResponse::success($response, 'Détails de la demande d\'adhésion');
    }

    /**
     * Récupérer les bénéficiaires existants d'un utilisateur
     */
    public function getBeneficiairesExistants()
    {
        $user = Auth::user();
        
        // Récupérer l'assuré principal de l'utilisateur
        $assurePrincipal = Assure::where('user_id', $user->id)
            ->where('est_principal', true)
            ->first();

        if (!$assurePrincipal) {
            return ApiResponse::success([
                'beneficiaires' => [],
                'message' => 'Aucun assuré principal trouvé'
            ], 'Aucun bénéficiaire existant');
        }

        // Récupérer tous les bénéficiaires liés à cet assuré principal
        $beneficiaires = Assure::where('assure_principal_id', $assurePrincipal->id)
            ->where('est_principal', false)
            ->with(['reponsesQuestionnaire.question'])
            ->get();

        $beneficiairesFormatted = $beneficiaires->map(function ($beneficiaire) {
            return [
                'id' => $beneficiaire->id,
                'nom' => $beneficiaire->nom,
                'prenoms' => $beneficiaire->prenoms,
                'date_naissance' => $beneficiaire->date_naissance,
                'sexe' => $beneficiaire->sexe,
                'lien_parente' => $beneficiaire->lien_parente,
                'photo_url' => $beneficiaire->photo_url,
                'statut' => $beneficiaire->statut,
                'created_at' => $beneficiaire->created_at,
                'reponses_questionnaire' => $beneficiaire->reponsesQuestionnaire->map(function ($reponse) {
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
        });

        return ApiResponse::success([
            'beneficiaires' => $beneficiairesFormatted,
            'total' => $beneficiairesFormatted->count(),
            'assure_principal' => [
                'id' => $assurePrincipal->id,
                'nom' => $assurePrincipal->nom,
                'prenoms' => $assurePrincipal->prenoms,
            ]
        ], 'Bénéficiaires existants récupérés avec succès');
    }

    /**
     * Supprimer un bénéficiaire existant
     */
    public function supprimerBeneficiaire($beneficiaireId)
    {
        $user = Auth::user();
        
        // Vérifier que le bénéficiaire appartient bien à l'utilisateur connecté
        $assurePrincipal = Assure::where('user_id', $user->id)
            ->where('est_principal', true)
            ->first();

        if (!$assurePrincipal) {
            return ApiResponse::error('Assuré principal non trouvé', 404);
        }

        $beneficiaire = Assure::where('id', $beneficiaireId)
            ->where('assure_principal_id', $assurePrincipal->id)
            ->where('est_principal', false)
            ->first();

        if (!$beneficiaire) {
            return ApiResponse::error('Bénéficiaire non trouvé ou accès non autorisé', 404);
        }

        // Supprimer les réponses du bénéficiaire
        ReponseQuestionnaire::where('personne_type', Assure::class)
            ->where('personne_id', $beneficiaire->id)
            ->delete();

        // Supprimer le bénéficiaire
        $beneficiaire->delete();

        return ApiResponse::success(null, 'Bénéficiaire supprimé avec succès');
    }







    public function download($id)
    {
        $demande = DemandeAdhesion::with([
            'user',
            'user.entreprise',
            'user.prestataire',
            'user.assure',
            'validePar', // validePar est déjà un Personnel
            'reponsesQuestionnaire' => function ($query) use ($id) {
                $query->where('demande_adhesion_id', $id);
            },
            'reponsesQuestionnaire.question',
            'assures' => function ($query) use ($id) {
                $query->where('demande_adhesion_id', $id);
            },
            'assures.reponsesQuestionnaire' => function ($query) use ($id) {
                $query->where('demande_adhesion_id', $id);
            },
            'assures.reponsesQuestionnaire.question',
            'employes' => function ($query) use ($id) {
                $query->where('demande_adhesion_id', $id);
            },
            'employes.reponsesQuestionnaire' => function ($query) use ($id) {
                $query->where('demande_adhesion_id', $id);
            },
            'employes.reponsesQuestionnaire.question',
            'assures.beneficiaires' => function ($query) use ($id) {
                $query->where('demande_adhesion_id', $id);
            },
            'assures.beneficiaires.reponsesQuestionnaire' => function ($query) use ($id) {
                $query->where('demande_adhesion_id', $id);
            },
            'assures.beneficiaires.reponsesQuestionnaire.question'
        ])->find($id);

        if (!$demande) {
            return ApiResponse::error('Demande d\'adhésion non trouvée', 404);
        }

        // Calculer les statistiques
        $statistiques = $this->calculerStatistiquesDemande($demande);

        // Préparer les données pour le PDF
        $data = [
            'demande' => $demande,
            'baseUrl' => url('/'), // URL de base pour les fichiers
            'statistiques' => $statistiques,
        ];

        // Choisir le template selon le type de demandeur
        $template = $this->getTemplateByDemandeurType($demande->type_demandeur);

        // Générez le PDF
        $pdf = Pdf::loadView($template, $data);

        // Retournez le PDF en téléchargement
        return $pdf->download("{$demande->nom}-{$demande->prenoms}.pdf");
    }

    /**
     * Détermine le template PDF à utiliser selon le type de demandeur
     */
    private function getTemplateByDemandeurType($typeDemandeur)
    {
        return match($typeDemandeur->value) {
            TypeDemandeurEnum::PHYSIQUE->value => 'pdf.demande-adhesion-physique',
            TypeDemandeurEnum::ENTREPRISE->value => 'pdf.demande-adhesion-entreprise',
            default => 'pdf.demande-adhesion-prestataire', // Pour tous les types de prestataires
        };
    }

    /**
     * Calculer les statistiques pour une demande d'adhésion
     */
    private function calculerStatistiquesDemande($demande)
    {
        $toutesLesPersonnes = collect();
        
        // Ajouter l'assuré principal s'il existe
        if ($demande->user && $demande->user->assure) {
            $toutesLesPersonnes->push($demande->user->assure);
        }
        
        // Ajouter les employés (vérifier si la relation existe)
        if ($demande->employes) {
            $toutesLesPersonnes = $toutesLesPersonnes->merge($demande->employes);
        }
        
        // Ajouter les bénéficiaires (vérifier si la relation existe)
        if ($demande->beneficiaires) {
            $toutesLesPersonnes = $toutesLesPersonnes->merge($demande->beneficiaires);
        }

        // Statistiques par âge
        $repartitionAge = [
            '18-25' => $toutesLesPersonnes->filter(function ($personne) {
                $age = $personne->date_naissance ? now()->diffInYears($personne->date_naissance) : 0;
                return $age >= 18 && $age <= 25;
            })->count(),
            '26-35' => $toutesLesPersonnes->filter(function ($personne) {
                $age = $personne->date_naissance ? now()->diffInYears($personne->date_naissance) : 0;
                return $age >= 26 && $age <= 35;
            })->count(),
            '36-45' => $toutesLesPersonnes->filter(function ($personne) {
                $age = $personne->date_naissance ? now()->diffInYears($personne->date_naissance) : 0;
                return $age >= 36 && $age <= 45;
            })->count(),
            '46-55' => $toutesLesPersonnes->filter(function ($personne) {
                $age = $personne->date_naissance ? now()->diffInYears($personne->date_naissance) : 0;
                return $age >= 46 && $age <= 55;
            })->count(),
            '55+' => $toutesLesPersonnes->filter(function ($personne) {
                $age = $personne->date_naissance ? now()->diffInYears($personne->date_naissance) : 0;
                return $age > 55;
            })->count(),
        ];

        // Statistiques par sexe
        $repartitionSexe = [
            'hommes' => $toutesLesPersonnes->where('sexe', 'M')->count(),
            'femmes' => $toutesLesPersonnes->where('sexe', 'F')->count(),
        ];

        return [
            'total_personnes' => $toutesLesPersonnes->count(),
            'total_employes' => $demande->employes ? $demande->employes->count() : 0,
            'total_beneficiaires' => $demande->beneficiaires ? $demande->beneficiaires->count() : 0,
            'repartition_age' => $repartitionAge,
            'repartition_sexe' => $repartitionSexe,
            'assure_principal' => $demande->user && $demande->user->assure ? [
                'nom' => $demande->user->assure->nom,
                'prenoms' => $demande->user->assure->prenoms,
                'photo_url' => $demande->user->assure->photo_url,
            ] : null,
        ];
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
                'contrat_propose',
                [
                    'demande_id' => $demande->id,
                    'contrat_id' => $contrat->id,
                    'type_contrat' => $contrat->type_contrat,
                    'prime_standard' => $contrat->prime_standard,
                    'propose_par' => $technicien->personnel->nom . ' ' . ($technicien->personnel->prenoms ?? ''),
                    'date_proposition' => now()->format('d/m/Y à H:i'),
                    'type' => 'contrat_propose'
                ]
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
                'statut' => $propositionContrat->statut?->value ?? $propositionContrat->statut,
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

            // Valider la demande via le service
            $demande = $this->demandeAdhesionService->validerDemande($demande, $medecinControleur->personnel);

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
     * Récupérer les contrats disponibles pour proposition
     */
    public function getContratsDisponibles()
    {
        return $this->demandeAdhesionService->getContratsDisponibles();
    }

    /**
     * Consulter les liens d'invitation existants pour une entreprise
     */
    public function consulterLiensInvitation(Request $request)
    {
        return $this->demandeAdhesionService->getLiensInvitation(Auth::user());
    }

    /**
     * Consulter les demandes d'adhésion d'une entreprise
     */
    public function mesDemandesAdhesion(Request $request)
    {
        $user = Auth::user();

        // Vérifier que l'utilisateur est une entreprise
        if (!$user->hasRole('entreprise')) {
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
        if (!$user->hasRole('entreprise')) {
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
        $invitation = InvitationEmploye::create([
            'entreprise_id' => $entrepriseId,
            'token' => InvitationEmploye::generateToken(),
            'expire_at' => now()->addDays(7),
        ]);
        $url = config('app.frontend_url') . "/employes/formulaire/{$invitation->token}";
        return ApiResponse::success([
            'invitation_id' => $invitation->id,
            'token' => $invitation->token,
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
                'email' => $data['email'] ?? null,
                'date_naissance' => $data['date_naissance'],
                'sexe' => $data['sexe'],
                'contact' => $data['contact'] ?? null,
                'est_principal' => true,
                'profession' => $data['profession'] ?? null,
                'photo' => isset($data['photo']) ? ImageUploadHelper::uploadImage($data['photo'], 'uploads/employes') : null,
            ]);
            // Enregistrer les réponses au questionnaire
            foreach ($data['reponses'] as $reponse) {
                $this->demandeAdhesionService->enregistrerReponsePersonne('employe', $assure->id, $reponse, null);
            }
                         // Enregistrer les bénéficiaires (optionnels)
             if (isset($data['beneficiaires']) && is_array($data['beneficiaires'])) {
                 foreach ($data['beneficiaires'] as $beneficiaire) {
                     // Créer le bénéficiaire directement pour les employés
                     $benefAssure = Assure::create([
                         'user_id' => null,
                         'assure_principal_id' => $assure->id,
                         'nom' => $beneficiaire['nom'],
                         'prenoms' => $beneficiaire['prenoms'],
                         'date_naissance' => $beneficiaire['date_naissance'],
                         'sexe' => $beneficiaire['sexe'],
                         'lien_parente' => $beneficiaire['lien_parente'],
                         'est_principal' => false,
                         'photo' => $beneficiaire['photo'] ?? null,
                     ]);

                     // Enregistrer les réponses du bénéficiaire
                     foreach ($beneficiaire['reponses'] as $reponse) {
                         $this->demandeAdhesionService->enregistrerReponsePersonne('beneficiaire', $benefAssure->id, $reponse, null);
                     }
                 }
             }
            // Notifier l'entreprise
            $entreprise = $invitation->entreprise;
            if ($entreprise && $entreprise->user && $entreprise->user->email) {
                // Envoyer l'email
                $this->notificationService->sendEmail(
                    $entreprise->user->email,
                    'Nouvelle fiche employé soumise',
                    'emails.nouvelle_fiche_employe',
                    [
                        'assure' => $assure,
                        'entreprise' => $entreprise,
                    ]
                );

                // Créer une notification in-app pour l'entreprise
                $this->notificationService->createNotification(
                    $entreprise->user->id,
                    'Nouvelle fiche employé soumise',
                    "L'employé {$assure->nom} {$assure->prenoms} a soumis sa fiche d'adhésion.",
                    'info',
                    [
                        'employe_id' => $assure->id,
                        'employe_nom' => $assure->nom,
                        'employe_prenoms' => $assure->prenoms,
                        'employe_email' => $assure->email,
                        'date_soumission' => now()->format('d/m/Y à H:i'),
                        'type' => 'nouvelle_fiche_employe'
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


    


    /**
     * Statistiques des demandes d'adhésion
     */
    public function stats()
    {
        return $this->demandeAdhesionService->getStats(Auth::user());
    }

    /**
     * Récupérer la liste des employés qui ont soumis leur demande d'adhésion
     * Pour le dashboard de l'entreprise
     */
    public function employesAvecDemandes(Request $request)
    {
        $user = Auth::user();

        // Vérifier que l'utilisateur est une entreprise
        if (!$user->hasRole('entreprise')) {
            return ApiResponse::error('Seules les entreprises peuvent consulter les demandes de leurs employés.', 403);
        }   

        $entreprise = $user->entreprise;

        // Récupérer tous les employés de l'entreprise avec leurs demandes d'adhésion
        $employes = Assure::where('entreprise_id', $entreprise->id)
            ->get();

        return ApiResponse::success($employes, 'Employés avec demandes d\'adhésion récupérés avec succès');
    }

    /**
     * Statistiques des employés et demandes d'adhésion pour le dashboard de l'entreprise
     */
    public function statistiquesEmployes(Request $request)
    {
        $user = Auth::user();

        // Vérifier que l'utilisateur est une entreprise
        if (!$user->hasRole('entreprise')) {
            return ApiResponse::error('Seules les entreprises peuvent consulter les statistiques de leurs employés.', 403);
        }

        $entreprise = $user->entreprise;

        // Récupérer tous les employés principaux de l'entreprise qui ont répondu au questionnaire
        $employesPrincipaux = Assure::where('entreprise_id', $entreprise->id)
            ->whereNull('assure_principal_id') // Assurés principaux uniquement
            ->get();

        $employesPrincipauxIds = $employesPrincipaux->pluck('id');

        // Récupérer tous les bénéficiaires des employés de cette entreprise
        $beneficiairesEmployes = Assure::whereIn('assure_principal_id', $employesPrincipauxIds)
            ->get();

        // Statistiques générales
        $totalEmployesPrincipaux = $employesPrincipaux->count();
        $totalEmployesEtBeneficiaires = $totalEmployesPrincipaux + $beneficiairesEmployes->count();

        // Statistiques par sexe - Employés principaux
        $employesPrincipauxHommes = $employesPrincipaux->where('sexe', 'M')->count();
        $employesPrincipauxFemmes = $employesPrincipaux->where('sexe', 'F')->count();

        // Statistiques par sexe - Bénéficiaires des employés
        $beneficiairesHommes = $beneficiairesEmployes->where('sexe', 'M')->count();
        $beneficiairesFemmes = $beneficiairesEmployes->where('sexe', 'F')->count();

        // Statistiques par sexe - Total des bénéficiaires (employés + leurs bénéficiaires)
        $totalBeneficiairesHommes = $employesPrincipauxHommes + $beneficiairesHommes;
        $totalBeneficiairesFemmes = $employesPrincipauxFemmes + $beneficiairesFemmes;

        // Répartition par âge - Employés principaux ET leurs bénéficiaires
        $repartitionAgeEmployesPrincipauxEtBeneficiaire = [
            '0-17' => $employesPrincipaux->filter(function ($employe) {
                if (!$employe->date_naissance) return false;
                $dateNaissance = \Carbon\Carbon::parse($employe->date_naissance);
                $age = $dateNaissance->age;
                return $age >= 0 && $age <= 17;
            })->count() + $beneficiairesEmployes->filter(function ($beneficiaire) {
                if (!$beneficiaire->date_naissance) return false;
                $dateNaissance = \Carbon\Carbon::parse($beneficiaire->date_naissance);
                $age = $dateNaissance->age;
                return $age >= 0 && $age <= 17;
            })->count(),
            '18-25' => $employesPrincipaux->filter(function ($employe) {
                if (!$employe->date_naissance) return false;
                $dateNaissance = \Carbon\Carbon::parse($employe->date_naissance);
                $age = $dateNaissance->age;
                return $age >= 18 && $age <= 25;
            })->count() + $beneficiairesEmployes->filter(function ($beneficiaire) {
                if (!$beneficiaire->date_naissance) return false;
                $dateNaissance = \Carbon\Carbon::parse($beneficiaire->date_naissance);
                $age = $dateNaissance->age;
                return $age >= 18 && $age <= 25;
            })->count(),
            '26-35' => $employesPrincipaux->filter(function ($employe) {
                if (!$employe->date_naissance) return false;
                $dateNaissance = \Carbon\Carbon::parse($employe->date_naissance);
                $age = $dateNaissance->age;
                return $age >= 26 && $age <= 35;
            })->count() + $beneficiairesEmployes->filter(function ($beneficiaire) {
                if (!$beneficiaire->date_naissance) return false;
                $dateNaissance = \Carbon\Carbon::parse($beneficiaire->date_naissance);
                $age = $dateNaissance->age;
                return $age >= 26 && $age <= 35;
            })->count(),
            '36-45' => $employesPrincipaux->filter(function ($employe) {
                if (!$employe->date_naissance) return false;
                $dateNaissance = \Carbon\Carbon::parse($employe->date_naissance);
                $age = $dateNaissance->age;
                return $age >= 36 && $age <= 45;
            })->count() + $beneficiairesEmployes->filter(function ($beneficiaire) {
                if (!$beneficiaire->date_naissance) return false;
                $dateNaissance = \Carbon\Carbon::parse($beneficiaire->date_naissance);
                $age = $dateNaissance->age;
                return $age >= 36 && $age <= 45;
            })->count(),
            '46-55' => $employesPrincipaux->filter(function ($employe) {
                if (!$employe->date_naissance) return false;
                $dateNaissance = \Carbon\Carbon::parse($employe->date_naissance);
                $age = $dateNaissance->age;
                return $age >= 46 && $age <= 55;
            })->count() + $beneficiairesEmployes->filter(function ($beneficiaire) {
                if (!$beneficiaire->date_naissance) return false;
                $dateNaissance = \Carbon\Carbon::parse($beneficiaire->date_naissance);
                $age = $dateNaissance->age;
                return $age >= 46 && $age <= 55;
            })->count(),
            '55+' => $employesPrincipaux->filter(function ($employe) {
                if (!$employe->date_naissance) return false;
                $dateNaissance = \Carbon\Carbon::parse($employe->date_naissance);
                $age = $dateNaissance->age;
                return $age > 55;
            })->count() + $beneficiairesEmployes->filter(function ($beneficiaire) {
                if (!$beneficiaire->date_naissance) return false;
                $dateNaissance = \Carbon\Carbon::parse($beneficiaire->date_naissance);
                $age = $dateNaissance->age;
                return $age > 55;
            })->count(),
        ];

        // Répartition par âge - Total des bénéficiaires (employés + leurs bénéficiaires)
        $repartitionAgeTotalBeneficiaires = [
            '0-17' => $repartitionAgeEmployesPrincipauxEtBeneficiaire['0-17'],
            '18-25' => $repartitionAgeEmployesPrincipauxEtBeneficiaire['18-25'],
            '26-35' => $repartitionAgeEmployesPrincipauxEtBeneficiaire['26-35'],
            '36-45' => $repartitionAgeEmployesPrincipauxEtBeneficiaire['36-45'],
            '46-55' => $repartitionAgeEmployesPrincipauxEtBeneficiaire['46-55'],
            '55+' => $repartitionAgeEmployesPrincipauxEtBeneficiaire['55+'],
        ];

        // Statistiques des réponses au questionnaire
        $employesAvecReponses = $employesPrincipaux->filter(function ($employe) {
            return $employe->reponsesQuestionnaire->count() > 0;
        })->count();

        $totalReponses = $employesPrincipaux->sum(function ($employe) {
            return $employe->reponsesQuestionnaire->count();
        });

        $statistiques = [
            'general' => [
                'total_employes_principaux' => $totalEmployesPrincipaux,
                'total_employes_et_beneficiaires' => $totalEmployesEtBeneficiaires, // Employés + leurs bénéficiaires
            ],
            'repartition' => [
                'par_sexe' => [
                    'total_beneficiaires' => [ // Employés + leurs bénéficiaires
                        'hommes' => $totalBeneficiairesHommes,
                        'femmes' => $totalBeneficiairesFemmes,
                    ],
                ],
                'par_age' => [
                    'total_beneficiaires' => $repartitionAgeTotalBeneficiaires,
                ],
            ],
        ];

        return ApiResponse::success($statistiques, 'Statistiques des employés récupérées avec succès.');
    }



    /**
     * Récupérer les contrats proposés pour un client
     */
    public function getContratsProposes()
    {
        try {
            $user = Auth::user();
            
            // Récupérer les propositions de contrats pour ce client
            $propositions = PropositionContrat::with([
                'demandeAdhesion.user',
                'contrat.garanties.categorieGarantie',
                'technicien.personnel'
            ])
            ->whereHas('demandeAdhesion', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->where('statut', StatutPropositionContratEnum::PROPOSEE->value)
            ->get();

            $contratsProposes = $propositions->map(function ($proposition) {
                // Grouper les garanties par catégorie
                $categoriesGaranties = $proposition->contrat->garanties->groupBy('categorie_garantie_id')
                    ->map(function ($garanties, $categorieId) {
                        $categorie = $garanties->first()->categorieGarantie;
                        $garantiesList = $garanties->pluck('nom')->implode(', ');
                        
                        return [
                            'libelle' => $categorie->nom,
                            'garanties' => $garantiesList
                        ];
                    })->values();

                return [
                    'proposition_id' => $proposition->id,
                    'contrat' => [
                        'id' => $proposition->contrat->id,
                        'nom' => $proposition->contrat->nom,
                        'type_contrat' => $proposition->contrat->type_contrat,
                        'description' => $proposition->contrat->description
                    ],
                    'details_proposition' => [
                        'prime_proposee' => $proposition->prime_proposee,
                        'taux_couverture' => $proposition->taux_couverture,
                        'frais_gestion' => $proposition->frais_gestion,
                        'commentaires_technicien' => $proposition->commentaires_technicien,
                        'date_proposition' => $proposition->date_proposition
                    ],
                    'categories_garanties' => $categoriesGaranties,
                    'statut' => $proposition->statut?->value ?? $proposition->statut
                ];
            });

            return ApiResponse::success($contratsProposes, 'Contrats proposés récupérés avec succès');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des contrats proposés', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return ApiResponse::error('Erreur lors de la récupération des contrats proposés: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Accepter une proposition de contrat
     */
    public function accepterContrat(int $propositionId)
    {
        try {
            $user = Auth::user();
            
            // Récupérer la proposition
            $proposition = PropositionContrat::with([
                'demandeAdhesion.user',
                'contrat',
                'technicien.personnel'
            ])->findOrFail($propositionId);

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
                // 1. Créer le contrat final
                $contrat = Contrat::create([
                    'user_id' => $proposition->demandeAdhesion->user_id,
                    'proposition_contrat_id' => $proposition->id,
                    'type_contrat' => $proposition->contrat->type_contrat,
                    'prime' => $proposition->prime_proposee,
                    'taux_couverture' => $proposition->taux_couverture,
                    'frais_gestion' => $proposition->frais_gestion,
                    'statut' => StatutContratEnum::ACTIF->value,
                    'date_debut' => now(),
                    'date_fin' => now()->addYear(),
                ]);

                // 2. Mettre à jour la proposition
                $proposition->update([
                    'statut' => StatutPropositionContratEnum::ACCEPTEE->value,
                    'date_acceptation' => now()
                ]);

                // 3. Mettre à jour la demande d'adhésion
                $proposition->demandeAdhesion->update([
                    'statut' => StatutDemandeAdhesionEnum::ACCEPTEE->value,
                    'contrat_id' => $contrat->id
                ]);

                // 4. Notification au technicien
                $this->notificationService->createNotification(
                    $proposition->technicien->user_id,
                    'Contrat accepté par le client',
                    "Le client {$proposition->demandeAdhesion->user->nom} a accepté votre proposition de contrat.",
                    'contrat_accepte_technicien',
                    [
                        'client_nom' => $proposition->demandeAdhesion->user->nom,
                        'contrat_nom' => $proposition->contrat->nom,
                        'prime' => $contrat->prime,
                        'type' => 'contrat_accepte_technicien'
                    ]
                );

                // 5. Notification au client
                $this->notificationService->createNotification(
                    $user->id,
                    'Contrat accepté avec succès',
                    "Votre contrat d'assurance est maintenant actif.",
                    'contrat_accepte',
                    [
                        'contrat_id' => $contrat->id,
                        'date_debut' => $contrat->date_debut,
                        'prime' => $contrat->prime,
                        'type' => 'contrat_accepte'
                    ]
                );

                DB::commit();

                return ApiResponse::success([
                    'contrat_id' => $contrat->id,
                    'message' => 'Contrat accepté avec succès'
                ], 'Contrat accepté avec succès');

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

            return ApiResponse::error('Erreur lors de l\'acceptation du contrat: ' . $e->getMessage(), 500);
        }
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
                'contrat_id' => 'required|exists:contrats,id',
                'prestataires' => 'required|array',
                'prestataires.pharmacies' => 'array',
                'prestataires.centres_soins' => 'array',
                'prestataires.optiques' => 'array',
                'prestataires.laboratoires' => 'array',
                'prestataires.centres_diagnostic' => 'array',
            ]);

            $client = User::findOrFail($request->client_id);
            $contrat = Contrat::findOrFail($request->contrat_id);

            // Vérifier que le contrat appartient au client
            if ($contrat->user_id !== $client->id) {
                return ApiResponse::error('Ce contrat n\'appartient pas au client spécifié', 400);
            }

            DB::beginTransaction();

            try {
                // 1. Créer l'entrée dans la table client_contrat
                $clientContrat = ClientContrat::create([
                    'client_id' => $client->id,
                    'contrat_id' => $contrat->id,
                    'type_client' => $client->type_demandeur ?? 'physique',
                    'date_debut' => $contrat->date_debut,
                    'date_fin' => $contrat->date_fin,
                    'statut' => 'ACTIF'
                ]);

                // 2. Assigner les prestataires
                $prestatairesAssignes = [];
                foreach ($request->prestataires as $type => $prestataireIds) {
                    foreach ($prestataireIds as $prestataireId) {
                        // Vérifier que le prestataire existe
                        $prestataire = Prestataire::find($prestataireId);
                        if (!$prestataire) {
                            throw new \Exception("Prestataire ID {$prestataireId} non trouvé");
                        }

                        $clientPrestataire = ClientPrestataire::create([
                            'client_contrat_id' => $clientContrat->id,
                            'prestataire_id' => $prestataireId,
                            'type_prestataire' => $type,
                            'statut' => 'ACTIF'
                        ]);

                        $prestatairesAssignes[] = [
                            'id' => $prestataire->id,
                            'nom' => $prestataire->nom,
                            'type' => $type,
                            'adresse' => $prestataire->adresse
                        ];
                    }
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
                    StatutDemandeAdhesionEnum::EN_PROPOSITION->value,
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
     * Récupérer la liste des prestataires pour le technicien (avec recherche)
     */
    public function getPrestatairesTechnicien(Request $request)
    {
        try {
            // Vérifier que l'utilisateur est un technicien
            if (!Auth::user()->hasRole('technicien')) {
                return ApiResponse::error('Accès non autorisé', 403);
            }

            $query = Prestataire::where('statut', StatutPrestataireEnum::VALIDE->value);

            // Recherche par nom ou adresse
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('nom', 'like', "%{$search}%")
                      ->orWhere('adresse', 'like', "%{$search}%");
                });
            }

            // Filtrer par type de prestataire
            if ($request->has('type_prestataire')) {
                $query->where('type_prestataire', $request->type_prestataire);
            }

            $prestataires = $query->get()->map(function ($prestataire) {
                return [
                    'id' => $prestataire->id,
                    'nom' => $prestataire->nom,
                    'type_prestataire' => $prestataire->type_prestataire?->value ?? $prestataire->type_prestataire,
                    'adresse' => $prestataire->adresse,
                    'telephone' => $prestataire->telephone,
                    'email' => $prestataire->email,
                    'statut' => $prestataire->statut?->value ?? $prestataire->statut
                ];
            });

            return ApiResponse::success($prestataires, 'Liste des prestataires récupérée avec succès');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des prestataires', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return ApiResponse::error('Erreur lors de la récupération des prestataires: ' . $e->getMessage(), 500);
        }
    }
}
