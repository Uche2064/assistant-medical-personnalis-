<?php

namespace App\Http\Controllers\v1\Api;

use App\Enums\StatutValidationEnum;
use App\Enums\TypeDemandeurEnum;
use App\Enums\TypeDonneeEnum;
use App\Helpers\ApiResponse;
use App\Helpers\ImageUploadHelper;
use App\Helpers\PdfUploadHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\DemandeAdhesionClientFormRequest;
use App\Http\Requests\DemandeAdhesionEntrepriseFormRequest;
use App\Http\Requests\DemandeAdhesionPrestataireFormRequest;
use App\Http\Requests\DemandeAdhesionRejectFormRequest;
use App\Models\DemandeAdhesion;
use App\Models\Question;
use App\Models\ReponsesQuestionnaire;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DemandeAdhesionController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index(Request $request)
    {
        $query = DemandeAdhesion::with('validePar', 'reponsesQuestionnaire');

        // Filtrage par statut si fourni
        $status = $request->query('statut');
        if ($status) {
            switch ($status) {
                case 'en_attente':
                    $query->pending();
                    break;
                case 'valide':
                    $query->validated();
                    break;
                case 'rejete':
                    $query->rejected();
                    break;
            }
        }

        // Filtrage par type de demandeur si fourni
        if ($request->has('type_demande')) {
            $query->where('type_demande', $request->query('type_demande'));
        }

        $demandes = $query->orderBy('created_at', 'desc')->get();

        if ($demandes->isEmpty()) {
            return ApiResponse::success([], 'Aucune demande d\'adhésion trouvée');
        }

        return ApiResponse::success($demandes, 'Liste des demandes d\'adhésion récupérée avec succès');
    }

    /**
     * Soumettre une nouvelle demande d'adhésion de prestataire
     */
    public function storePrestataire(DemandeAdhesionPrestataireFormRequest $request)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            $basicData = [
                'nom' => $data['nom'] ?? null,
                'prenoms' => $data['prenoms'] ?? null,
                'raison_sociale' => $data['raison_sociale'] ?? null,
                'email' => $data['email'],
                'contact' => $data['contact'],
                'type_demande' => $data['type_demande'],
                'adresse' => $data['adresse'],
                'statut' => StatutValidationEnum::EN_ATTENTE,
            ];

            $demande = DemandeAdhesion::create($basicData);

            if (isset($data['reponses']) && is_array($data['reponses'])) {
                $this->storeReponses($demande, $data['reponses']);
            }

            DB::commit();

            $this->notificationService->sendEmail($data['email'], 'Demande d\'adhésion enregistrée', 'emails.en_attente', [
                'demande' => $demande,
            ]);

            return ApiResponse::success([
                'demande_id' => $demande->id,
                'statut' => $demande->statut->value
            ], 'Demande d\'adhésion de prestataire enregistrée avec succès', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Soumettre une nouvelle demande d'adhésion pour une entreprise
     */
    public function storeEntreprise(DemandeAdhesionEntrepriseFormRequest $request)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            // Création de la demande d'adhésion
            $demande = DemandeAdhesion::create([
                'raison_sociale' => $data['raison_sociale'],
                'email' => $data['email'],
                'contact' => $data['contact'],
                'type_demande' => TypeDemandeurEnum::PROSPECT_MORAL->value,
                'statut' => StatutValidationEnum::EN_ATTENTE,
                'adresse' => $data['adresse'],
            ]);

            // Traitement des réponses au questionnaire si présentes
            if (isset($data['reponses']) && is_array($data['reponses'])) {
                $this->storeReponses($demande, $data['reponses']);
            }

            DB::commit();


            return ApiResponse::success([
                'demande_id' => $demande->id,
                'statut' => $demande->statut->value
            ], 'Demande d\'adhésion d\'entreprise enregistrée avec succès', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Soumettre une nouvelle demande d'adhésion pour un prospect physique
     */
    public function storeClient(DemandeAdhesionClientFormRequest $request)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            // Création de la demande d'adhésion
            $demande = DemandeAdhesion::create([
                'nom_demandeur' => $data['nom'],
                'prenoms_demandeur' => $data['prenoms'],
                'email' => $data['email'] ?? null,
                'contact' => $data['contact'],
                'type_demande' => TypeDemandeurEnum::PROSPECT_PHYSIQUE->value, // Type client individuel
                'statut' => StatutValidationEnum::EN_ATTENTE->value,
                'profession' => $data['profession'] ?? null,
                'adresse' => $data['adresse'],
                'date_naissance' => $data['date_naissance'],
                'sexe' => $data['sexe'] ?? null
            ]);

            // Traitement des réponses au questionnaire si présentes
            if (isset($data['reponses']) && is_array($data['reponses'])) {
                $this->storeReponses($demande, $data['reponses']);
            }


            DB::commit();

            return ApiResponse::success([
                'demande_id' => $demande->id,
                'statut' => $demande->statut->value
            ], 'Demande d\'adhésion enregistrée avec succès', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Récupère les questions pour les prospects physiques
     * avec un formulaire vide pour la demande d'adhésion
     */
    public function getClientFormulaire()
    {
        // Récupérer les questions actives pour les prospects
        $questions = Question::where('destinataire', TypeDemandeurEnum::PROSPECT_PHYSIQUE->value)
            ->where('est_actif', true)
            ->orderBy('id', 'asc')
            ->get(['id', 'libelle', 'type_donnees', 'obligatoire']);

        // Préparer le formulaire avec les champs de base et les questions dynamiques
        $formulaire = [
            'champs' => [
                'nom',
                'prenoms',
                'email',
                'contact',
                'date_naissance',
                'adresse',
                'profession',
                'sexe',
                'adresse',
            ],
            'questions' => $questions
        ];

        return ApiResponse::success($formulaire, 'Formulaire pour prospect physique récupéré avec succès');
    }


    public function getPrestataireFormulaire(Request $request)
    {
        $typePrestataire = $request->query('type_demandeur');
        $validTypes = [
            TypeDemandeurEnum::PHARMACIE->value,
            TypeDemandeurEnum::LABORATOIRE_CENTRE_DIAGNOSTIC->value,
            TypeDemandeurEnum::CENTRE_DE_SOINS->value,
            TypeDemandeurEnum::OPTIQUE->value,
            TypeDemandeurEnum::MEDECIN_LIBERAL->value,
        ];



        if (!in_array($typePrestataire, $validTypes)) {
            return ApiResponse::error('Type de prestataire invalide', 400);
        }

        // Champs de base communs à tous les types de prestataires
        $champsCommuns = [
            'email',
            'contact',
            'type_demande',
        ];

        // Définition des champs spécifiques selon le type de prestataire
        $champsSpecifiques = [];

        // Pour prestataire physique (médecin libéral)
        if (!$typePrestataire || $typePrestataire === TypeDemandeurEnum::MEDECIN_LIBERAL->value) {
            $champsSpecifiques[TypeDemandeurEnum::MEDECIN_LIBERAL->value] = [
                'nom',
                'prenoms',
                'adresse',
            ];
        }

        // Pour prestataires moraux (pharmacie, laboratoire, centre de soins, optique)
        $prestairesMoraux = [
            TypeDemandeurEnum::PHARMACIE->value,
            TypeDemandeurEnum::LABORATOIRE_CENTRE_DIAGNOSTIC->value,
            TypeDemandeurEnum::CENTRE_DE_SOINS->value,
            TypeDemandeurEnum::OPTIQUE->value,
        ];

        foreach ($prestairesMoraux as $typeMoral) {
            if (!$typePrestataire || $typePrestataire === $typeMoral) {
                $champsSpecifiques[$typeMoral] = [
                    'raison_sociale',
                ];
            }
        }

        // Récupération des questions spécifiques au(x) type(s) demandé(s)
        $questions = [];

        if ($typePrestataire) {
            // Si un type spécifique est demandé
            $questions[$typePrestataire] = Question::where('destinataire', $typePrestataire)
                ->where('est_actif', true)
                ->orderBy('id', 'asc')
                ->get(['id', 'libelle', 'type_donnees', 'obligatoire', 'options']);
        } else {
            // Si aucun type spécifique n'est demandé, retourner toutes les questions par type
            foreach ($validTypes as $type) {
                $questions[$type] = Question::where('destinataire', $type)
                    ->where('est_actif', true)
                    ->orderBy('id', 'asc')
                    ->get(['id', 'libelle', 'type_donnees', 'obligatoire', 'options']);
            }
        }

        // Construction du résultat
        $result = [
            'types_prestataires' => $validTypes,
            'champs_communs' => $champsCommuns,
            'champs_specifiques' => $champsSpecifiques,
            'questions' => $questions
        ];

        return ApiResponse::success($result, 'Formulaire pour prestataire récupéré avec succès');
    }

    //Retourne les informations pour le formulaire de demande d'adhésion entreprise
    public function getEntrepriseFormulaire()
    {
        // Récupérer les questions actives pour les entreprises (prospects moraux)
        $questions = Question::where('destinataire', TypeDemandeurEnum::PROSPECT_MORAL->value)
            ->where('est_actif', true)
            ->orderBy('id', 'asc')
            ->get(['id', 'libelle', 'type_donnees', 'obligatoire', 'options']);

        // Préparer le formulaire avec les champs de base et les questions dynamiques
        $formulaire = [
            'champs' => [
                'raison_sociale',
                'email',
                'contact',
                'adresse',
            ],
            'questions' => $questions,
        ];

        return ApiResponse::success($formulaire, 'Formulaire pour entreprise récupéré avec succès');
    }


    /**
     * Afficher les détails d'une demande d'adhésion
     */
    public function show(int $id)
    {
        $demande = DemandeAdhesion::with([
            'validePar',
            'reponsesQuestionnaire'
        ])->find($id);

        if (!$demande) {
            return ApiResponse::error('Demande d\'adhésion non trouvée', 404);
        }

        return ApiResponse::success($demande, 'Détails de la demande d\'adhésion');
    }

    /**
     * Valider une demande d'adhésion (réservé au personnel)
     */
    public function validate(int $id)
    {
        // Récupérer le personnel connecté
        $personnel = Auth::user()->personnel;

        if (!$personnel) {
            return ApiResponse::error('Seul le personnel autorisé peut valider une demande', 403);
        }

        $demande = DemandeAdhesion::find($id);

        if (!$demande) {
            return ApiResponse::error('Demande d\'adhésion non trouvée', 404);
        }

        if (!$demande->isPending()) {
            return ApiResponse::error('Cette demande a été validé', 400);
        }

        try {
            // Validation de la demande
            $demande->validate($personnel);
            $this->notificationService->sendEmail($demande->email, 'Demande d\'adhésion validée', 'emails.acceptee', [
                'demande' => $demande,
            ]);

            // TODO: Créer l'utilisateur et le prestataire/client correspondant
            // Cette partie sera implémentée selon le type de demande

            return ApiResponse::success([
                'demande_id' => $demande->id,
                'statut' => $demande->statut->value,
                'validee_par' => $personnel->user->nom . ' ' . ($personnel->user->prenoms ?? '')
            ], 'Demande d\'adhésion validée avec succès');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Rejeter une demande d'adhésion (réservé au personnel)
     */
    public function reject(DemandeAdhesionRejectFormRequest $request, int $id)
    {
        // Récupérer le personnel connecté
        $personnel = Auth::user()->personnel;

        if (!$personnel) {
            return ApiResponse::error('Seul le personnel autorisé peut rejeter une demande', 403);
        }

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
            $demande->reject($personnel, $validatedData['motif_rejet']);
            $this->notificationService->sendEmail($demande->email, 'Demande d\'adhésion rejetée', 'emails.rejetee', [
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

    // méthode privée

    /**
     * Stockage des réponses aux questionnaires pour une demande d'adhésion
     * 
     * @param DemandeAdhesion $demande L'objet demande d'adhésion
     * @param array $reponses Les réponses à stocker
     * @return void
     */
    private function storeReponses(DemandeAdhesion $demande, array $reponses)
    {
        if (empty($reponses)) {
            return;
        }

        $reponsesTraitees = [];

        // Parcourir les réponses
        foreach ($reponses as $key => $value) {
            // Cas 1: C'est un fichier
            if (is_object($value) && method_exists($value, 'getClientOriginalName')) {
                $fichier = $value;
                $mimeType = $fichier->getMimeType();
                $storagePath = 'demandes_adhesion/' . $demande->id . '/documents';
                
                // Traiter selon le type de fichier
                if (str_starts_with($mimeType, 'image/')) {
                    // Image
                    $fileUrl = ImageUploadHelper::uploadImage($fichier, $storagePath);
                    if ($fileUrl) {
                        $reponsesTraitees[$key] = [
                            'type' => 'fichier',
                            'chemin' => $fileUrl,
                            'nom_original' => $fichier->getClientOriginalName(),
                            'mime_type' => $mimeType
                        ];
                    }
                } elseif ($mimeType === 'application/pdf') {
                    // PDF
                    $result = PdfUploadHelper::storePdf(
                        file_get_contents($fichier->getRealPath()),
                        $storagePath,
                        $fichier->getClientOriginalName()
                    );
                    if ($result) {
                        $reponsesTraitees[$key] = [
                            'type' => 'fichier',
                            'chemin' => $result['url'],
                            'nom_original' => $fichier->getClientOriginalName(),
                            'mime_type' => $mimeType
                        ];
                    }
                } else {
                    // Autre type de fichier
                    $fileName = uniqid('doc_') . '_' . time() . '.' . $fichier->getClientOriginalExtension();
                    $filePath = $fichier->storeAs($storagePath, $fileName, 'public');
                    $reponsesTraitees[$key] = [
                        'type' => 'fichier',
                        'chemin' => asset('storage/' . $filePath),
                        'nom_original' => $fichier->getClientOriginalName(),
                        'mime_type' => $mimeType
                    ];
                }
            } 
            // Cas 2: C'est un objet clé-valeur
            else {
                $reponsesTraitees[$key] = [
                    'type' => 'texte',
                    'valeur' => $value
                ];
            }
        }

        // Vérifier si une entrée existe déjà pour cette demande d'adhésion
        $existingReponse = ReponsesQuestionnaire::where('demande_adhesion_id', $demande->id)->first();

        if ($existingReponse) {
            // Mettre à jour l'entrée existante
            $existingReponse->update([
                'reponses' => json_encode($reponsesTraitees),
            ]);
            $this->notificationService->sendEmail($demande->email, 'Demande d\'adhésion mise à jour', 'emails.en_attente', [
                'demande' => $demande,
            ]);
        } else {
            // Créer une nouvelle entrée
            ReponsesQuestionnaire::create([
                'demande_adhesion_id' => $demande->id,
                'reponses' => json_encode($reponsesTraitees),
            ]);
            $this->notificationService->sendEmail($demande->email, 'Demande d\'adhésion enregistrée', 'emails.en_attente', [
                'demande' => $demande,
            ]);
        }
    }
}
