<?php

namespace App\Services;

use App\Enums\RoleEnum;
use App\Enums\StatutDemandeAdhesionEnum;
use App\Enums\TypeDemandeurEnum;
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
        $user = Auth::user()->load(['client']);
        if (!$user || !$user->client) {
            return false;
        }

        return DemandeAdhesion::where('client_id', $user->client->id)
            ->where('statut', $statut)
            ->exists();

    }

    /**
     * Créer une demande d'adhésion pour une personne physique ou entreprise
     */
    public function createDemandeAdhesionClient(array $data, User $user): DemandeAdhesion
    {
        $client = $user->client;
        
        if (!$client) {
            throw new \Exception('Aucun client trouvé pour cet utilisateur');
        }
        
        $typeDemandeur = $data['type_demandeur'];

        // Créer la demande d'adhésion
        $demande = DemandeAdhesion::create([
            'type_demandeur' => $typeDemandeur,
            'statut' => StatutDemandeAdhesionEnum::EN_ATTENTE->value,
            'client_id' => $client->id,
        ]);

        // Enregistrer les réponses au questionnaire
        if (isset($data['reponses'])) {
            foreach ($data['reponses'] as $reponse) {
                $this->enregistrerReponse($reponse, $demande->id, $user);
            }
        }

        // Enregistrer les bénéficiaires si fournis (uniquement pour les personnes physiques)
        if (isset($data['beneficiaires'])) {
            foreach ($data['beneficiaires'] as $beneficiaire) {
                $this->enregistrerBeneficiaire($demande, $beneficiaire);
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
    private function enregistrerBeneficiaire($demande, array $beneficiaireData): void
    {
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
            'photo_url' => $beneficiaireData['photo_url'] ?? null,
            'personne_id' => $beneficiairePersonne->id,
            'password' => Hash::make($plainPassword),
            'est_actif' => false,
            'mot_de_passe_a_changer' => true,
        ]);

        $beneficiaireUser->assignRole(RoleEnum::CLIENT->value);

        $beneficiaireAssure = Assure::create([
            'client_id' => $demande->client_id,
            'est_principal' => false,
            'lien_parente' => $beneficiaireData['lien_parente'],
            'user_id' => $beneficiaireUser->id,
            'assure_principal_id' => $demande->user->assure->id
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
