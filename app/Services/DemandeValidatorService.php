<?php

namespace App\Services;

use App\Enums\RoleEnum;
use App\Enums\StatutDemandeAdhesionEnum;
use App\Enums\TypeDemandeurEnum;
use App\Helpers\ImageUploadHelper;
use App\Models\Assure;
use App\Models\Client;
use App\Models\DemandeAdhesion;
use App\Models\Personne;
use App\Models\ReponseQuestionnaire;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class DemandeValidatorService
{
    protected NotificationService $notificationService;

    public function __construct(
        NotificationService $notificationService,
    ) {
        $this->notificationService = $notificationService;
    }
    public function hasPendingDemande(): bool
    {
        return $this->hasDemandeWithStatut(StatutDemandeAdhesionEnum::EN_ATTENTE->value);
    }

    public function hasValidatedDemande(): bool
    {
        return $this->hasDemandeWithStatut(StatutDemandeAdhesionEnum::VALIDEE->value);
    }

    private function hasDemandeWithStatut(string $statut): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        return DemandeAdhesion::where('user_id', $user->id)
            ->where('statut', $statut)
            ->exists();

    }

    /**
     * Créer une demande d'adhésion pour une personne physique ou entreprise
     */
    public function createDemandeAdhesionClient(array $data, User $user): DemandeAdhesion
    {

        $typeDemandeur = $data['type_demandeur'];

        // Créer la demande d'adhésion
        $demande = DemandeAdhesion::create([
            'type_demandeur' => $typeDemandeur,
            'statut' => StatutDemandeAdhesionEnum::EN_ATTENTE->value,
            'user_id' => $user->id,
        ]);

        // Créer le Client s'il n'existe pas déjà
        $client = $user->client;
        if (!$client) {
            $client = Client::create([
                'user_id' => $user->id,
                'type_client' => $typeDemandeur === TypeDemandeurEnum::CLIENT->value
                    ? \App\Enums\ClientTypeEnum::PHYSIQUE->value
                    : \App\Enums\ClientTypeEnum::MORAL->value,
            ]);
        }

        // Créer l'Assure principal s'il n'existe pas déjà
        $assurePrincipal = $user->assure;
        if (!$assurePrincipal) {
            $assurePrincipal = Assure::create([
                'user_id' => $user->id,
                'client_id' => $client->id,
                'est_principal' => true,
                'lien_parente' => null, // Pas de lien de parenté pour l'assuré principal
            ]);
        }

        // Enregistrer les réponses au questionnaire
        if (isset($data['reponses'])) {
            foreach ($data['reponses'] as $reponse) {
                $this->enregistrerReponse($reponse, $demande->id, $user);
            }
        }

        // Enregistrer les bénéficiaires si fournis (uniquement pour les personnes physiques)
        if (isset($data['beneficiaires'])) {
            foreach ($data['beneficiaires'] as $beneficiaire) {
                $this->enregistrerBeneficiaire($demande, $beneficiaire, $client->id, $assurePrincipal->id);
            }
        }

        return $demande;
    }

    /**
     * Créer une demande d'adhésion pour un prestataire
     */
    public function createDemandeAdhesionPrestataire(array $data, User $user): DemandeAdhesion
    {
        $typeDemandeur = $data['type_demandeur'];

        // Créer la demande d'adhésion
        $demande = DemandeAdhesion::create([
            'type_demandeur' => $typeDemandeur,
            'statut' => StatutDemandeAdhesionEnum::EN_ATTENTE->value,
            'user_id' => $user->id,
        ]);


        // Enregistrer les réponses au questionnaire principal
        foreach ($data['reponses'] as $reponse) {
            $this->enregistrerReponse($reponse, $demande->id, $user);
        }

        return $demande;
    }

    /**
     * Vérifier si un fichier est uploadé
     */
    private function isUploadedFile($value): bool
    {
        return is_object($value) && method_exists($value, 'getClientOriginalName');
    }

    /**
     * Enregistrer une réponse de personne (assuré principal ou bénéficiaire)
     */
    private function enregistrerReponse(array $reponseData, $demandeId, User $user): void
    {
        $reponse = [
            'question_id' => $reponseData['question_id'],
            'demande_adhesion_id' => $demandeId,
            'date_reponse' => now(),
            'user_id' => $user->id
        ];

        // Ajouter la valeur selon le type de question
        if (isset($reponseData['reponse'])) {
            $reponse['reponse'] = $reponseData['reponse'];
        }
        ReponseQuestionnaire::create($reponse);
    }

    /**
     * Enregistrer un bénéficiaire
     */
    private function enregistrerBeneficiaire($demande, array $beneficiaireData, int $clientId, int $assurePrincipalId): void
    {
        // check unique email
        if (!empty($beneficiaireData['email']) && User::where('email', $beneficiaireData['email'])->exists()) {
            throw new \Exception("L'email du bénéficiaire est déjà utilisé");
        }

        if(isset($beneficiaireData['contact']) && !empty($beneficiaireData['contact'])) {
            if (User::where('contact', $beneficiaireData['contact'])->exists()) {
                throw new \Exception("Le contact du bénéficiaire est déjà utilisé");
            }
        }

        // Traiter la photo du bénéficiaire si fournie
        $photoUrl = null;
        if (isset($beneficiaireData['photo_url']) && $this->isUploadedFile($beneficiaireData['photo_url'])) {
            $emailFolder = $beneficiaireData['email'] ?? $demande->user->email;
            $photoUrl = ImageUploadHelper::uploadImage(
                $beneficiaireData['photo_url'],
                'uploads',
                $emailFolder
            );

            if (!$photoUrl) {
                throw new \Exception('Erreur lors de l\'upload de la photo du bénéficiaire');
            }
        }

        // Créer la personne bénéficiaire
        $beneficiairePersonne = Personne::create([
            'nom' => $beneficiaireData['nom'],
            'prenoms' => $beneficiaireData['prenoms'],
            'date_naissance' => $beneficiaireData['date_naissance'],
            'sexe' => $beneficiaireData['sexe'],
            'profession' => $beneficiaireData['profession'] ?? null,
        ]);

        $plainPassword = User::genererMotDePasse();
        $beneficiaireUser = User::create([
            'email' => $beneficiaireData['email'] ?? null,
            'contact' => $beneficiaireData['contact'] ?? null,
            'adresse' => $beneficiaireData['adresse'] ?? null,
            'photo_url' => $photoUrl,
            'personne_id' => $beneficiairePersonne->id,
            'password' => Hash::make($plainPassword),
            'est_actif' => false,
            'mot_de_passe_a_changer' => true,
        ]);

        $beneficiaireUser->assignRole(RoleEnum::CLIENT->value);

        $beneficiaireAssure = Assure::create([
            'client_id' => $clientId,
            'est_principal' => false,
            'lien_parente' => $beneficiaireData['lien_parente'],
            'user_id' => $beneficiaireUser->id,
            'assure_principal_id' => $assurePrincipalId
        ]);

        // Envoyer les identifiants si email fourni
        if (!empty($beneficiaireData['email'])) {
            $this->notificationService->sendCredentials($beneficiaireUser, $plainPassword);
        }

        // Enregistrer les réponses au questionnaire du bénéficiaire
        if (!empty($beneficiaireData['reponses'])) {
            foreach ($beneficiaireData['reponses'] as $reponse) {
                $this->enregistrerReponse($reponse, $demande->id, $beneficiaireUser);
            }
        }
    }
}
