<?php

namespace App\Http\Controllers\v1\Api\demande_adhesion;

use App\Enums\EmailType;
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
use App\Http\Requests\ValiderProspectDemande;
use App\Jobs\SendEmailJob;
use App\Models\Contrat;
use App\Models\DemandeAdhesion;
use App\Models\Prospect;
use App\Models\Question;
use App\Models\ReponsesQuestionnaire;
use App\Services\DemandeValidatorService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DemandeAdhesionController extends Controller
{
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
        $query = DemandeAdhesion::with('validePar', 'reponsesQuestionnaire');

        Log::info('Recherche par ' . $request->input('search'));
        Log::info('Statut: ' . $request->input('statut'));
        Log::info('Type_demande: ' . $request->input('type_demande'));

        // 🔒 Filtrage basé sur le rôle de l'utilisateur
        if ($user->hasRole('technicien')) {
            $query->whereIn('type_demandeur', ['physique', 'moral']);
        } elseif ($user->hasRole('medecin_controleur')) {
            $query->where('type_demandeur', 'prestataire');
        }

        //  Filtrage par statut si fourni
        $status = $request->input('statut');
        if ($status) {
            $query->where('statut', match ($status) {
                'en_attente' => StatutValidationEnum::EN_ATTENTE->value,
                'validee'    => StatutValidationEnum::VALIDEE->value,
                'rejetee'    => StatutValidationEnum::REJETEE->value,
                default      => null
            });
        }

        // Filtrage explicite par type_demande si fourni
        if ($request->has('type_demandeur')) {
            $query->where('type_demandeur', $request->input('type_demandeur'));
        }

        // 🔁 Pagination
        $perPage = $request->query('per_page', 10);
        $demandes = $query->orderByDesc('created_at')->paginate($perPage);

        return ApiResponse::success($demandes, 'Liste des demandes d\'adhésion récupérée avec succès');
    }



    /**
     * Soumettre une nouvelle demande d'adhésion de prestataire
     */
    public function storePrestataire(DemandeAdhesionPrestataireFormRequest $request)
    {
        $data = $request->validated();
        if ($this->demandeValidatorService->hasValidatedDemande($data)) {
            return ApiResponse::error("Vous avez déjà une demande acceptée.", 422);
        }

        if ($this->demandeValidatorService->hasPendingDemande($data)) {
            return ApiResponse::error("Vous avez déjà une demande en attente.", 422);
        }
        try {
            DB::beginTransaction();

            $prospect = Prospect::create([
                'nom' => $data['nom'] ?? null,
                'prenoms' => $data['prenoms'] ?? null,
                'raison_sociale' => $data['raison_sociale'] ?? null,
                'email' => $data['email'],
                'contact' => $data['contact'],
                'adresse' => $data['adresse'],
            ]);

            $demande = DemandeAdhesion::create([
                'type_demandeur' => $data['type_prestataire'],
                'prospect_id' => $prospect->id,
                'statut' => StatutValidationEnum::EN_ATTENTE->value,
            ]);

            if (isset($data['reponses']) && is_array($data['reponses'])) {
                $this->storeReponses($demande, $data['reponses']);
            }

            DB::commit();

            $this->notificationService->sendEmail($data['email'], 'Demande d\'adhésion enregistrée', EmailType::EN_ATTENTE->value, [
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
        if ($this->demandeValidatorService->hasValidatedDemande($data)) {
            return ApiResponse::error("Vous avez déjà une demande acceptée.", 422);
        }

        if ($this->demandeValidatorService->hasPendingDemande($data)) {
            return ApiResponse::error("Vous avez déjà une demande en attente.", 422);
        }
        try {
            DB::beginTransaction();

            // Création de la demande d'adhésion
            $prospect = Prospect::create([
                'raison_sociale' => $data['raison_sociale'],
                'email' => $data['email'],
                'contact' => $data['contact'],
                'adresse' => $data['adresse'],
            ]);

            $demande = DemandeAdhesion::create([
                'type_demandeur' => TypeDemandeurEnum::PROSPECT_MORAL->value,
                'prospect_id' => $prospect->id,
                'statut' => StatutValidationEnum::EN_ATTENTE->value,
                'code_parainage' => $data['code_parainage'] ?? null,
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
    public function storeProspectPhysique(DemandeAdhesionClientFormRequest $request)
    {
        $data = $request->validated();
        if ($this->demandeValidatorService->hasValidatedDemande($data)) {
            return ApiResponse::error("Vous avez déjà une demande acceptée.", 422);
        }

        if ($this->demandeValidatorService->hasPendingDemande($data)) {
            return ApiResponse::error("Vous avez déjà une demande en attente.", 422);
        }

        try {
            DB::beginTransaction();
            // Création du prospect physique
            $prospect = $this->createProspectData($data);


            // stockage de la demande d'adhésion
            $demande = $this->createDemandeAdhesion($prospect, $data['code_parainage']);


            // traitement des réponses au questionnaire si présentes
            if (isset($data['reponses']) && is_array($data['reponses'])) {
                $this->storeReponses($demande, $data['reponses']);
            }

            if (!empty($data['beneficiaires']) && is_array($data['beneficiaires'])) {
                $this->storeBeneficiaires($demande, $data['beneficiaires']);
            }


            // envoyer un email de confirmation
            $this->notificationService->sendEmail($data['email'], 'Demande d\'adhésion enregistrée', EmailType::EN_ATTENTE->value, [
                'demande' => $demande,
            ]);

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

    protected function createProspectData(Prospect $data): Prospect
    {
        return Prospect::create([
            'nom' => $data['nom'],
            'prenoms' => $data['prenoms'],
            'email' => $data['email'],
            'contact' => $data['contact'],
            'profession' => $data['profession'] ?? null,
            'adresse' => $data['adresse'],
            'date_naissance' => $data['date_naissance'],
            'sexe' => $data['sexe'],
            'photo_url' => $data['photo_url'],
            'nombre_de_beneficaire' => $data['nombre_de_beneficaire'] ?? 0,
        ]);
    }

    protected function createDemandeAdhesion(Prospect $prospect, String $codeParainage): DemandeAdhesion
    {
        return DemandeAdhesion::create([
            'type_demandeur' => TypeDemandeurEnum::PROSPECT_PHYSIQUE,
            'prospect_id' => $prospect->id,
            'statut' => StatutValidationEnum::EN_ATTENTE,
            'code_parainage' => $codeParainage,
        ]);
    }

    protected function storeBeneficiaires(DemandeAdhesion $demande, array $beneficiaires): void
    {
        foreach ($beneficiaires as $item) {
            $photoUrl = null;

            if (isset($item['photo']) && $item['photo']->isValid()) {
                $photoUrl = $item['photo']->store("beneficiaires/{$demande->id}", 'public');
            }

            $demande->beneficiaires()->create([
                'nom' => $item['nom'],
                'prenoms' => $item['prenoms'],
                'date_naissance' => $item['date_naissance'],
                'lien_parente' => $item['lien_parente'],
                'photo_url' => $photoUrl,
            ]);
        }
    }



    public function getQuestionsForDemandeur($typePrestataire)
    {

        $result = [
            "champs_de_base" => $this->getFieldsForDemandeur($typePrestataire),
            "questions" => Question::forDestinataire($typePrestataire)->get(),
        ];


        if ($result) {
            return ApiResponse::success($result, 'Questions pour ' . $typePrestataire . ' récupérées avec succès');
        } else {
            return ApiResponse::error('Aucune question trouvée pour les prestataires', 404);
        }
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
    public function validerProspect(ValiderProspectDemande $request, int $id)
    {
        $data = $request->validated();

        $technicien = Auth::user();

        $demande = DemandeAdhesion::with('prospect')->find($id);


        if (!$demande) {
            return ApiResponse::error('Demande d\'adhésion non trouvée', 404);
        }

        if (!$demande->isPending()) {
            return ApiResponse::error('Cette demande a été validé', 400);
        }

        try {
            // Validation de la demande
            $demande->validate($technicien->personnel);
            $this->notificationService->sendEmail($demande->prospect->email, 'Demande d\'adhésion validée', EmailType::ACCEPTED->value, [
                'demande' => $demande,
                'technicien' => $technicien,
                'contrat' => Contrat::where('type_contrat', $data['type_contrat'])
                    ->first()
            ]);

            // TODO: Créer l'utilisateur et le prestataire/client correspondant
            // Cette partie sera implémentée selon le type de demande

            return ApiResponse::success([
                'demande_id' => $demande->id,
                'statut' => $demande->statut->value,
                'validee_par' => $technicien->nom . ' ' . ($technicien->prenoms ?? '')
            ], 'Demande d\'adhésion validée avec succès');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }


    public function validerPrestataire(Request $request, int $id)
    {

        $medecinControleur = Auth::user();

        $demande = DemandeAdhesion::with('prospect')->find($id);

        if (!$demande) {
            return ApiResponse::error('Demande d\'adhésion non trouvée', 404);
        }

        if (!$demande->isPending()) {
            return ApiResponse::error('Cette demande a été validée', 400);
        }

        try {
            // Validation de la demande
            $demande->validate($medecinControleur->personnel);
            $this->notificationService->sendEmail($demande->prospect->email, 'Demande d\'adhésion validée', EmailType::ACCEPTED->value, [
                'demande' => $demande,
                'medecin_controleur' => $medecinControleur,
                'contrat' => Contrat::where('type_contrat', $request->input('type_contrat'))
                    ->first()
            ]);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Rejeter une demande d'adhésion (réservé au personnel)
     */
    public function rejeter(DemandeAdhesionRejectFormRequest $request, int $id)
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

    private function getFieldsForDemandeur($typePrestataire)
    {
        $champsDeBase = [
            "email",
            "contact",
            "adresse"
        ];

        if ($typePrestataire == TypeDemandeurEnum::PROSPECT_PHYSIQUE->value) {
            $champsDeBase = array_merge($champsDeBase, [
                "nom",
                "prenoms",
                "date_naissance",
                "sexe",
                "photo_url",
                "profession",
            ]);
        } elseif ($typePrestataire == TypeDemandeurEnum::PROSPECT_MORAL->value) {
            $champsDeBase = array_merge($champsDeBase, [
                "raison_sociale",
                "nombre_employes",
                "fiches_medicales_employes",
            ]);
        }

        return $champsDeBase;
    }

    /**
     * Stockage des réponses aux questionnaires pour une demande d'adhésion
     * 
     * @param DemandeAdhesion $demande L'objet demande d'adhésion
     * @param array $reponses Les réponses à stocker
     * @return void
     */
    private function storeReponses(DemandeAdhesion $demande, array $reponses): void
    {
        if (empty($reponses)) return;

        $questions = Question::whereIn('id', array_keys($reponses))->get()->keyBy('id');

        foreach ($reponses as $questionId => $valeur) {
            $question = $questions[$questionId] ?? null;
            if (!$question) continue;

            // Liste de fichiers
            if (is_array($valeur) && isset($valeur[0]) && $this->isUploadedFile($valeur[0])) {
                foreach ($valeur as $fichier) {
                    $this->enregistrerFichier($demande, $questionId, $fichier);
                }
            }
            // Fichier unique
            elseif ($this->isUploadedFile($valeur)) {
                $this->enregistrerFichier($demande, $questionId, $valeur);
            }
            // Valeur simple
            else {
                $this->enregistrerValeur($demande, $question, $questionId, $valeur);
            }
        }
    }

    private function isUploadedFile($value): bool
    {
        return is_object($value) && method_exists($value, 'getClientOriginalName');
    }

    private function enregistrerFichier(DemandeAdhesion $demande, int $questionId, $fichier): void
    {
        $mimeType = $fichier->getMimeType();
        $folder = 'demandes_adhesion/' . $demande->id . '/documents';

        if (str_starts_with($mimeType, 'image/')) {
            $url = ImageUploadHelper::uploadImage($fichier, $folder);
        } elseif ($mimeType === 'application/pdf') {
            $result = PdfUploadHelper::storePdf(file_get_contents($fichier->getRealPath()), $folder, $fichier->getClientOriginalName());
            $url = $result['url'] ?? null;
        } else {
            $filename = uniqid('doc_') . '_' . time() . '.' . $fichier->getClientOriginalExtension();
            $path = $fichier->storeAs($folder, $filename, 'public');
            $url = asset('storage/' . $path);
        }

        if ($url) {
            ReponsesQuestionnaire::create([
                'demande_adhesion_id' => $demande->id,
                'question_id' => $questionId,
                'reponse_fichier' => $url
            ]);
        }
    }

    private function enregistrerValeur(DemandeAdhesion $demande, $question, int $questionId, $valeur): void
    {
        $data = [
            'demande_adhesion_id' => $demande->id,
            'question_id' => $questionId,
        ];

        switch ($question->type_donnee) {
            case 'boolean':
                $data['reponse_bool'] = filter_var($valeur, FILTER_VALIDATE_BOOLEAN);
                break;
            case 'decimal':
                $data['reponse_decimal'] = floatval($valeur);
                break;
            case 'date':
                $data['reponse_date'] = $valeur;
                break;
            default:
                $data['reponses_text'] = $valeur;
                break;
        }

        ReponsesQuestionnaire::create($data);
    }
}
