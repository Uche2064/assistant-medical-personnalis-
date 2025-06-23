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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DemandeAdhesionController extends Controller
{
    /**
     * Récupérer toutes les demandes d'adhésion
     * Filtrable par statut (en_attente, valide, rejete)
     */
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
            $documents = [];

            if (isset($data['reponses']) && is_array($data['reponses'])) {
                $normalizedResponses = $this->normalizeReponses($data['reponses']);

                foreach ($normalizedResponses as $questionId => $reponseData) {
                    $question = Question::find($questionId);
                    if (!$question) continue;

                    if ($question->type_donnees === TypeDonneeEnum::FILE && isset($reponseData['reponse'])) {
                        $fileData = $this->handleFileUpload($reponseData['reponse'], 'demandes_adhesion/' . $demande->id . '/documents');
                        if ($fileData) {
                            $reponseData['reponse'] = $fileData;
                            $documents[$question->libelle] = $fileData;
                        }
                    }

                    ReponsesQuestionnaire::create([
                        'question_id' => $questionId,
                        'demande_adhesion_id' => $demande->id,
                        'reponse' => is_array($reponseData['reponse'])
                            ? json_encode($reponseData['reponse'])
                            : $reponseData['reponse']
                    ]);
                }

                $demande->infos_complementaires = ['documents' => $documents];
                $demande->save();
            }

            DB::commit();

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

            // Préparer les informations complémentaires avec les données de l'entreprise
            $infosComplementaires = [
                'secteur_activite' => $data['secteur_activite'] ?? null,
                'nombre_employes' => $data['nombre_employes'] ?? null,
            ];

            // Création de la demande d'adhésion
            $demande = DemandeAdhesion::create([
                'raison_sociale' => $data['raison_sociale'],
                'email' => $data['email'],
                'contact' => $data['contact'],
                'type_demande' => TypeDemandeurEnum::PROSPECT_MORAL->value,
                'statut' => StatutValidationEnum::EN_ATTENTE,
                'infos_complementaires' => $infosComplementaires
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
                'nom' => $data['nom'],
                'prenoms' => $data['prenoms'],
                'email' => $data['email'] ?? null,
                'contact' => $data['contact'],
                'type_demande' => TypeDemandeurEnum::PROSPECT_PHYSIQUE->value, // Type client individuel
                'statut' => StatutValidationEnum::EN_ATTENTE->value,
                'profession' => $data['profession'] ?? null,
                'adresse' => $data['adresse'],
                'date_naissance' => $data['date_naissance'],
                'sexe' => $data['sexe'] ?? null,

                // Stockage des informations supplémentaires en JSON
                'infos_complementaires' => json_encode([
                    'photo_url' => $data['photo_url'] ?? null,
                ])
            ]);

            // Traitement des réponses au questionnaire si présentes
            if (isset($data['reponses']) && is_array($data['reponses'])) {
                $this->storeReponses($demande, $data['reponses']);
            }

            DB::commit();

            return ApiResponse::success([
                'demande_id' => $demande->id,
                'statut' => $demande->statut->value
            ], 'Demande d\'adhésion de prospect enregistrée avec succès', 201);
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
            TypeDemandeurEnum::LABORATOIRE->value,
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
            TypeDemandeurEnum::LABORATOIRE->value,
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
            'infos_complementaires' => [
                'secteur_activite',
                'nombre_employes',
            ]
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

      //Stockage des réponses aux questionnaires pour une demande d'adhésion
      private function storeReponses(DemandeAdhesion $demande, array $reponses)
      {
          if (empty($reponses)) {
              return;
          }
  
          // Vérifier que les questions existent et sont valides pour le type de destinataire
          $questionIds = array_column($reponses, 'question_id');
          $validQuestions = Question::whereIn('id', $questionIds)
              ->where('est_actif', true)
              ->pluck('id')
              ->toArray();
  
          // Filtrer les réponses pour ne garder que celles correspondant à des questions valides
          $validReponses = array_filter($reponses, function ($reponse) use ($validQuestions) {
              return in_array($reponse['question_id'], $validQuestions);
          });
  
          if (empty($validReponses)) {
              return;
          }
  
          // Transformer le tableau de réponses en un tableau associatif question_id => reponse
          $reponsesArray = [];
          foreach ($validReponses as $reponse) {
  
              $reponsesArray[$reponse['question_id']] = $reponse['reponse'];
          }
  
          // Vérifier si une entrée existe déjà pour cette demande d'adhésion
          $existingReponse = ReponsesQuestionnaire::where('demande_adhesion_id', $demande->id)->first();
  
          if ($existingReponse) {
              // Mettre à jour l'entrée existante
              $existingReponse->update([
                  'reponses' => json_encode($reponsesArray),
                  'est_validee' => false, // Par défaut, les réponses ne sont pas validées
              ]);
          } else {
              // Créer une nouvelle entrée
              ReponsesQuestionnaire::create([
                  'demande_adhesion_id' => $demande->id,
                  'reponses' => json_encode($reponsesArray),
                  'est_validee' => false, // Par défaut, les réponses ne sont pas validées
              ]);
          }
      }


    private function normalizeReponses(array $reponses): array
    {
        $normalized = [];

        foreach ($reponses as $key => $value) {
            if (is_array($value) && isset($value['question_id'])) {
                $questionId = $value['question_id'];
                if (is_scalar($questionId)) {
                    $normalized[$questionId] = $value;
                }
            } elseif (is_numeric($key)) {
                $normalized[$key] = [
                    'question_id' => $key,
                    'reponse' => $value
                ];
            }
        }

        return $normalized;
    }


    private function handleFileUpload($file, $storagePath)
    {
        $mimeType = $file->getMimeType();
        $fileData = null;

        if (str_starts_with($mimeType, 'image/')) {
            $fileUrl = ImageUploadHelper::uploadImage($file, $storagePath);
            if ($fileUrl) {
                $path = str_replace(asset('storage/'), '', $fileUrl);
                $fileData = [
                    'path' => $path,
                    'url' => $fileUrl,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $mimeType,
                    'size' => $file->getSize()
                ];
            }
        } elseif ($mimeType === 'application/pdf') {
            $result = PdfUploadHelper::storePdf(
                file_get_contents($file->getRealPath()),
                $storagePath,
                $file->getClientOriginalName()
            );
            if ($result) {
                $fileData = [
                    'path' => $result['path'],
                    'url' => $result['url'],
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $mimeType,
                    'size' => $file->getSize()
                ];
            }
        } else {
            $fileName = uniqid('doc_') . '_' . time() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs($storagePath, $fileName, 'public');
            $fileData = [
                'path' => $filePath,
                'url' => asset('storage/' . $filePath),
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $mimeType,
                'size' => $file->getSize()
            ];
        }

        return $fileData;
    }
}
