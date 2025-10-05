<?php

namespace App\Http\Controllers\v1\Api\entreprise;

use App\Enums\TypeDemandeurEnum;
use App\Enums\StatutDemandeAdhesionEnum;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\demande_adhesion\SoumissionEmployeFormRequest;
use App\Http\Resources\DemandeAdhesionEntrepriseResource;
use App\Http\Resources\QuestionResource;
use App\Models\Assure;
use App\Models\DemandeAdhesion;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\DemandeAdhesionService;
use App\Helpers\ImageUploadHelper;
use App\Models\LienInvitation;
use App\Services\NotificationService;
use Carbon\Carbon;

class EntrepriseController extends Controller
{

    private $demandeAdhesionService;
    private $notificationService;

    public function __construct(
        DemandeAdhesionService $demandeAdhesionService,
        NotificationService $notificationService
    ) {
        $this->demandeAdhesionService = $demandeAdhesionService;
        $this->notificationService = $notificationService;
    }

    public function getInvitationLink(Request $request)
    {
        $user = Auth::user();
        // Vérifier que l'utilisateur est une entreprise
        if (!$user->client->isMoral()) {
            return ApiResponse::error('Seules les entreprises peuvent générer un lien d\'invitation.', 403);
        }
        $invitation = LienInvitation::where('client_id', $user->entreprise->id)
            ->where('expire_a', '>', now())
            ->first();

        if (!$invitation) {
            return ApiResponse::error('Aucun lien pour ce client', 404);
        }
        return ApiResponse::success([
            'invitation_id' => $invitation->id,
            'jeton' => $invitation->jeton,
            'url' => config('app.frontend_url') . "/employes/formulaire/{$invitation->jeton}",
            'expire_a' => $invitation->expire_a,
        ], 'Lien d\'invitation récupéré avec succès.');
    }

    /**
     * Générer un lien d'invitation unique pour qu'un employé remplisse sa fiche d'adhésion (un seul lien actif par entreprise)
     */
    public function genererLienInvitationEmploye(Request $request)
    {
        $user = Auth::user();
        // Vérifier que l'utilisateur est une entreprise
        if (!$user->client->isMoral()) {
            return ApiResponse::error('Seules les entreprises peuvent générer un lien d\'invitation.', 403);
        }
        $entrepriseId = $user->entreprise->id;
        // Chercher un lien actif existant
        $invitationExistante = LienInvitation::where('client_id', $entrepriseId)
            ->where('expire_a', '>', now())
            ->first();
        if ($invitationExistante) {
            $url = config('app.frontend_url') . "/employes/formulaire/{$invitationExistante->jeton}";
            return ApiResponse::success([
                'invitation_id' => $invitationExistante->id,
                'url' => $url,
                'jeton' => $invitationExistante->jeton,
                'expire_a' => $invitationExistante->expire_a,
            ], 'Lien d\'invitation déjà existant.');
        }
        // Sinon, générer un nouveau lien
        $invitation = LienInvitation::create([
            'client_id' => $entrepriseId,
            'jeton' => LienInvitation::genererToken(),
            'expire_a' => now()->addDays((int) env('TOKEN_LINK_EXPIRE_TIME_DAYS')),
        ]);
        $url = config('app.frontend_url') . "/employes/formulaire/{$invitation->jeton}";
        return ApiResponse::success([
            'invitation_id' => $invitation->id,
            'jeton' => $invitation->jeton,
            'url' => $url,
            'expire_a' => $invitation->expire_a,
        ], 'Nouveau lien d\'invitation généré avec succès.');
    }

    /**
     * Soumission groupée de la demande d'adhésion entreprise
     */
    public function soumettreDemandeAdhesionEntreprise(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasRole('client') || !$user->client->isMoral()) {
            return ApiResponse::error('Seules les entreprises peuvent soumettre une demande groupée.', 403);
        }
        $entreprise = $user->entreprise;
        $employes = Assure::where('client_id', $entreprise->id)->get();
        if ($employes->isEmpty()) {
            return ApiResponse::error('Aucun employé n\'a encore soumis sa fiche.', 422);
        }
        DB::beginTransaction();
        try {
            // Créer la demande d'adhésion entreprise
            $demande = DemandeAdhesion::create([
                'type_demandeur' => TypeDemandeurEnum::CLIENT->value,
                'statut' => StatutDemandeAdhesionEnum::EN_ATTENTE->value,
                'user_id' => $user->id,
            ]);
            // Notifier SUNU (technicien)
            $this->demandeAdhesionService->notifyByDemandeurType($demande, TypeDemandeurEnum::CLIENT->value);

            DB::commit();
            return ApiResponse::success(new DemandeAdhesionEntrepriseResource($demande), 'Demande d\'adhésion entreprise soumise avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Erreur lors de la soumission de la demande d\'adhésion entreprise : ' . $e->getMessage(), 500);
        }
    }

    /**
     * Consulter les demandes d'adhésion d'une entreprise
     */
    public function getDemandesAdhesions(Request $request)
    {
        $user = Auth::user();

        // Vérifier que l'utilisateur est une entreprise
        if (!$user->hasRole('client') || !$user->client->isMoral()) {
            return ApiResponse::error('Seules les entreprises peuvent soumettre une demande groupée.', 403);
        }

        $entreprise = $user->entreprise;

        $query = DemandeAdhesion::with([
            'user.entreprise',
            'validePar'
        ])
            ->where('user_id', $user->id)
            ->where('type_demandeur', TypeDemandeurEnum::CLIENT->value);


        // Pagination
        $demandes = $query->orderByDesc('created_at')->get();

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
                    ];
                }),
                // Statistiques
                'statistiques' => [
                    'total_employes' => $demande->assures->count(),
                    'total_employes_avec_beneficiaires' => $demande->assures->filter(function ($assure) {
                        return $assure->beneficiaires->count() > 0;
                    })->count() + $demande->assures->count(),
                ]
            ];
        });

        return ApiResponse::success([
            'demandes' => $demandesTransformees,
            // 'statistiques_globales' => [
            //     'total_demandes' => $demandes->total(),
            //     'demandes_en_attente' => $demandes->where('statut', StatutDemandeAdhesionEnum::EN_ATTENTE->value)->count(),
            //     'demandes_validees' => $demandes->where('statut', StatutDemandeAdhesionEnum::VALIDEE->value)->count(),
            //     'demandes_rejetees' => $demandes->where('statut', StatutDemandeAdhesionEnum::REJETEE->value)->count(),
            //     'demandes_en_proposition' => $demandes->where('statut', StatutDemandeAdhesionEnum::PROPOSEE->value)->count(),
            //     'demandes_acceptees' => $demandes->where('statut', StatutDemandeAdhesionEnum::ACCEPTEE->value)->count(),
            // ]
        ], 'Demandes d\'adhésion de l\'entreprise récupérées avec succès.');
    }

    /**
     * Consulter les demandes d'adhésion soumises par les employés de l'entreprise
     */
    public function demandesEmployes(Request $request)
    {
        $user = Auth::user();

        $entreprise = $user->entreprise;

        $query = DemandeAdhesion::with([
            'user',
            'validePar'
        ])->where('user_id', $user->id)->where('type_demandeur', TypeDemandeurEnum::CLIENT->value);

        // Pagination
        $demandes = $query->orderByDesc('created_at')->get();

        // if($demandes->isEmpty()) {
        //     return ApiResponse::success([], "Aucune demande des employés trouvées");
        // }
        // Transformer les données
        $demandesTransformees = $demandes->map(function ($demande) use ($entreprise) {
            // Récupérer les employés de cette demande qui appartiennent à l'entreprise
            $employes = $demande->assures->where('client_id', $entreprise->id);

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

                // Informations sur l'utilisateur qui a soumis la demande
                'demandeur' => [
                    'id' => $demande->user->id,
                    'email' => $demande->user->email,
                    'contact' => $demande->user->contact,
                    'adresse' => $demande->user->adresse,
                ]
            ];
        });


        return ApiResponse::success([
            'demandes' => $demandesTransformees,
            // 'statistiques_globales' => [
            //     'total_demandes' => $demandes->total(),
            //     'demandes_en_attente' => $demandes->where('statut', StatutDemandeAdhesionEnum::EN_ATTENTE->value)->count(),
            //     'demandes_validees' => $demandes->where('statut', StatutDemandeAdhesionEnum::VALIDEE->value)->count(),
            //     'demandes_rejetees' => $demandes->where('statut', StatutDemandeAdhesionEnum::REJETEE->value)->count(),
            // ]
        ], 'Demandes d\'adhésion des employés récupérées avec succès.');
    }

    /**
     * Statistiques des employés et demandes d'adhésion pour le dashboard de l'entreprise
     */
    public function statistiquesEmployes(Request $request)
    {
        $user = Auth::user();

        $entreprise = $user->entreprise;

        // Récupérer tous les employés principaux de l'entreprise qui ont répondu au questionnaire
        $employesPrincipaux = Assure::where('client_id', $entreprise->id)
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
                $dateNaissance = Carbon::parse($employe->date_naissance);
                $age = $dateNaissance->age;
                return $age >= 0 && $age <= 17;
            })->count() + $beneficiairesEmployes->filter(function ($beneficiaire) {
                if (!$beneficiaire->date_naissance) return false;
                $dateNaissance = Carbon::parse($beneficiaire->date_naissance);
                $age = $dateNaissance->age;
                return $age >= 0 && $age <= 17;
            })->count(),
            '18-25' => $employesPrincipaux->filter(function ($employe) {
                if (!$employe->date_naissance) return false;
                $dateNaissance = Carbon::parse($employe->date_naissance);
                $age = $dateNaissance->age;
                return $age >= 18 && $age <= 25;
            })->count() + $beneficiairesEmployes->filter(function ($beneficiaire) {
                if (!$beneficiaire->date_naissance) return false;
                $dateNaissance = Carbon::parse($beneficiaire->date_naissance);
                $age = $dateNaissance->age;
                return $age >= 18 && $age <= 25;
            })->count(),
            '26-35' => $employesPrincipaux->filter(function ($employe) {
                if (!$employe->date_naissance) return false;
                $dateNaissance = Carbon::parse($employe->date_naissance);
                $age = $dateNaissance->age;
                return $age >= 26 && $age <= 35;
            })->count() + $beneficiairesEmployes->filter(function ($beneficiaire) {
                if (!$beneficiaire->date_naissance) return false;
                $dateNaissance = Carbon::parse($beneficiaire->date_naissance);
                $age = $dateNaissance->age;
                return $age >= 26 && $age <= 35;
            })->count(),
            '36-45' => $employesPrincipaux->filter(function ($employe) {
                if (!$employe->date_naissance) return false;
                $dateNaissance = Carbon::parse($employe->date_naissance);
                $age = $dateNaissance->age;
                return $age >= 36 && $age <= 45;
            })->count() + $beneficiairesEmployes->filter(function ($beneficiaire) {
                if (!$beneficiaire->date_naissance) return false;
                $dateNaissance = Carbon::parse($beneficiaire->date_naissance);
                $age = $dateNaissance->age;
                return $age >= 36 && $age <= 45;
            })->count(),
            '46-55' => $employesPrincipaux->filter(function ($employe) {
                if (!$employe->date_naissance) return false;
                $dateNaissance = Carbon::parse($employe->date_naissance);
                $age = $dateNaissance->age;
                return $age >= 46 && $age <= 55;
            })->count() + $beneficiairesEmployes->filter(function ($beneficiaire) {
                if (!$beneficiaire->date_naissance) return false;
                $dateNaissance = Carbon::parse($beneficiaire->date_naissance);
                $age = $dateNaissance->age;
                return $age >= 46 && $age <= 55;
            })->count(),
            '55+' => $employesPrincipaux->filter(function ($employe) {
                if (!$employe->date_naissance) return false;
                $dateNaissance = Carbon::parse($employe->date_naissance);
                $age = $dateNaissance->age;
                return $age > 55;
            })->count() + $beneficiairesEmployes->filter(function ($beneficiaire) {
                if (!$beneficiaire->date_naissance) return false;
                $dateNaissance = Carbon::parse($beneficiaire->date_naissance);
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
        $statistiques = [
            'general' => [
                'total_employes_principaux' => $totalEmployesPrincipaux,
                'total_employes_et_beneficiaires' => $totalEmployesEtBeneficiaires, // Employés + leurs bénéficiaires
            ],
            'employes_assures' => $employesPrincipaux,
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

    // /**
    //  * Récupérer la liste des employés qui ont soumis leur demande d'adhésion
    //  * Pour le dashboard de l'entreprise
    //  */
    // public function employesAvecDemandes(Request $request)
    // {
    //     $user = Auth::user();


    //     $entreprise = $user->entreprise;

    //     // Récupérer tous les employés de l'entreprise avec leurs demandes d'adhésion
    //     $employes = Assure::where('client_id', $entreprise->id)
    //         ->get();

    //     return ApiResponse::success($employes, 'Employés avec demandes d\'adhésion récupérés avec succès');
    // }



    /**
     * Afficher le formulaire d'adhésion emloyé via le jeton d'invitation
     */
    public function showFormulaireEmploye($jeton)
    {
        $invitation = LienInvitation::where('jeton', $jeton)
            ->where('expire_a', '>', now())
            ->first();
        if (!$invitation) {
            return ApiResponse::error('Lien d\'invitation invalide ou expiré.', 404);
        }
        // Récupérer les questions actives pour le type CLIENT
        $questions = Question::active()->byDestinataire(TypeDemandeurEnum::CLIENT->value)->get();
        return ApiResponse::success([
            'entreprise' => $invitation->client,
            'jeton' => $jeton,
            'beneficiaires',
            'nom',
            'prenoms',
            'email',
            'date_naissance',
            'sexe',
            'contact',
            'adresse',
            'photo',
            'questions' => QuestionResource::collection($questions),

        ], 'Formulaire employé prêt à être rempli.');
    }

    /**
     * Soumettre la fiche employé via le lien d'invitation
     */
    public function soumettreFicheEmploye(SoumissionEmployeFormRequest $request, $jeton)
    {
        $invitation = LienInvitation::where('jeton', $jeton)->first();
        if (!$invitation) {
            return ApiResponse::error('Lien d\'invitation invalide.', 404);
        }
        if ($invitation->isExpired()) {
            return ApiResponse::error('Lien d\'invitation expiré.', 404);
        }
        $data = $request->validated();
        DB::beginTransaction();
        try {  
            // Créer l'assuré principal (employé)
            

            // Enregistrer les réponses aux questions
           

            // Enregistrer les bénéficiaires (optionnels)
            

            // Créer une notification in-app pour l'entreprise


            // $this->notificationService->createNotification(
            //     $invitation->entreprise->user->id,
            //     'Nouvelle fiche employé soumise',
            //     "L'employé {$assure->nom} {$assure->prenoms} a soumis sa fiche d'adhésion.",
            //     'info',
            //     [
            //         'employe_id' => $assure->id,
            //         'employe_nom' => $assure->nom,
            //         'employe_prenoms' => $assure->prenoms,
            //         'employe_email' => $assure->email,
            //         'date_soumission' => now()->format('d/m/Y à H:i'),
            //         'type' => 'nouvelle_fiche_employe'
            //     ]
            // );


            DB::commit();
            return ApiResponse::success(null, 'Fiche employé soumise avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Erreur lors de la soumission de la fiche employé : ' . $e->getMessage(), 500);
        }
    }
}
