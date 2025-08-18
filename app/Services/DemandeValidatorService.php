<?php

namespace App\Services;

use App\Enums\StatutDemandeAdhesionEnum;
use App\Enums\TypeDemandeurEnum;
use App\Models\Assure;
use App\Models\DemandeAdhesion;
use App\Models\ReponseQuestionnaire;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DemandeValidatorService
{
    public function hasPendingDemande(array $data): bool
    {
        return $this->hasDemandeWithStatut($data, StatutDemandeAdhesionEnum::EN_ATTENTE->value);
    }

    public function hasValidatedDemande(array $data): bool
    {
        return $this->hasDemandeWithStatut($data, StatutDemandeAdhesionEnum::VALIDEE->value);
    }

    private function hasDemandeWithStatut(array $data, string $statut): bool
    {
        return User::where(function ($query) use ($data) {
            // On vérifie email et contact obligatoirement
            $query->where('email', $data['email'] ?? Auth::user()->email)
                  ->orWhere('contact', $data['contact'] ?? Auth::user()->contact);
        })
        ->whereHas('demandes', function ($query) use ($statut) {
            $query->where('statut', $statut);
            $query->where('user_id', Auth::user()->id);    
        })
        ->exists();
    }

    /**
     * Créer une demande d'adhésion pour une personne physique ou entreprise
     */
    public function createDemandeAdhesionPhysique(array $data, User $user): DemandeAdhesion
    {
        $typeDemandeur = $data['type_demandeur'];

        // Créer la demande d'adhésion
        $demande = DemandeAdhesion::create([
            'type_demandeur' => $typeDemandeur,
            'statut' => StatutDemandeAdhesionEnum::EN_ATTENTE->value,
            'user_id' => $user->id,
        ]);

        // Pour les personnes physiques, mettre à jour l'assuré principal
        if ($typeDemandeur === TypeDemandeurEnum::PHYSIQUE->value) {
            $assurePrincipal = Assure::where('user_id', $user->id)->first();
            if ($assurePrincipal) {
                $assurePrincipal->update([
                    'demande_adhesion_id' => $demande->id,
                ]);
            }
        }

        // Enregistrer les réponses au questionnaire principal
        if ($typeDemandeur === TypeDemandeurEnum::PHYSIQUE->value) {
            // Pour les personnes physiques, les réponses sont liées à l'assuré
            $assurePrincipal = Assure::where('user_id', $user->id)->first();
            if ($assurePrincipal) {
                foreach ($data['reponses'] as $reponse) {
                    $this->enregistrerReponsePersonne('App\Models\Assure', $assurePrincipal->id, $reponse, $demande->id);
                }
            }
        } else {
            // Pour les autres types, les réponses sont liées à l'utilisateur
            foreach ($data['reponses'] as $reponse) {
                $this->enregistrerReponsePersonne('App\Models\User', $user->id, $reponse, $demande->id);
            }
        }

        // Enregistrer les bénéficiaires si fournis (uniquement pour les personnes physiques)
        if (!empty($data['beneficiaires']) && $typeDemandeur === TypeDemandeurEnum::PHYSIQUE->value) {
            $assurePrincipal = Assure::where('user_id', $user->id)->first();
            Log::info('Beneficiaires', ['beneficiaires' => $data['beneficiaires']]);
            if ($assurePrincipal) {
                foreach ($data['beneficiaires'] as $beneficiaire) {
                    $this->enregistrerBeneficiaire($demande, $beneficiaire, $assurePrincipal);
                }
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
            $this->enregistrerReponsePersonne('App\Models\User', $user->id, $reponse, $demande->id);
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
    private function enregistrerReponsePersonne($personneType, $personneId, array $reponseData, $demandeId): void
    {
        
        $reponse = [
            'question_id' => $reponseData['question_id'],
            'personne_id' => $personneId,
            'personne_type' => $personneType,
            'demande_adhesion_id' => $demandeId,
        ];

        // Ajouter la valeur selon le type de question
        if (isset($reponseData['reponse_text'])) {
            $reponse['reponse_text'] = $reponseData['reponse_text'];
        }
        if (isset($reponseData['reponse_number'])) {
            $reponse['reponse_number'] = $reponseData['reponse_number'];
        }
        if (isset($reponseData['reponse_bool'])) {
            $reponse['reponse_bool'] = $reponseData['reponse_bool'];
        }
        if (isset($reponseData['reponse_date'])) {
            $reponse['reponse_date'] = $reponseData['reponse_date'];
        }
        if (isset($reponseData['reponse_fichier'])) {
            // Traiter l'upload de fichier
            if ($this->isUploadedFile($reponseData['reponse_fichier'])) {
                $reponse['reponse_fichier'] = \App\Helpers\ImageUploadHelper::uploadImage(
                    $reponseData['reponse_fichier'], 
                    'uploads/demandes_adhesion/' . $demandeId
                );
            }
        }

        ReponseQuestionnaire::create($reponse);
    }

    /**
     * Enregistrer un bénéficiaire
     */
    private function enregistrerBeneficiaire($demande, array $beneficiaire, Assure $assurePrincipal): void
    {
        // Créer le bénéficiaire
        $beneficiaireAssure = Assure::create([
            'nom' => $beneficiaire['nom'],
            'prenoms' => $beneficiaire['prenoms'],
            'date_naissance' => $beneficiaire['date_naissance'],
            'sexe' => $beneficiaire['sexe'],
            'lien_parente' => $beneficiaire['lien_parente'],
            'profession' => $beneficiaire['profession'] ?? null,
            'contact' => $beneficiaire['contact'] ?? null,
            'photo' => $beneficiaire['photo_url'] ?? null,
            'est_principal' => false, // Le bénéficiaire n'est jamais principal
            'assure_principal_id' => $assurePrincipal->id, // Référence vers l'assuré principal
            'demande_adhesion_id' => $demande->id,
            'user_id' => null, // Les bénéficiaires n'ont pas de compte utilisateur
        ]);

        // Enregistrer les réponses au questionnaire du bénéficiaire
        if (!empty($beneficiaire['reponses'])) {
            foreach ($beneficiaire['reponses'] as $reponse) {
                $this->enregistrerReponsePersonne('App\Models\Assure', $beneficiaireAssure->id, $reponse, $demande->id);
            }
        }
    }


}
