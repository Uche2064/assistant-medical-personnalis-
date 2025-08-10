<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BeneficiaireResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'prenoms' => $this->prenoms,
            'date_naissance' => $this->date_naissance,
            'sexe' => $this->sexe,
            'lien_parente' => $this->lien_parente,
            'profession' => $this->profession,
            'photo' => $this->photo,
            'contact' => $this->contact,
            'demande_adhesion_id' => $this->demande_adhesion_id,
            'assure_principal_id' => $this->assure_principal_id,
            'contrat_id' => $this->contrat_id,
            'adresse' => $this->adresse,
            'reponses_questionnaire' => $this->reponsesQuestionnaire->map(function ($reponse) {
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
        ];
    }
}
