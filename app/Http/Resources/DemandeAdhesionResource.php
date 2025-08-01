<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DemandeAdhesionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $demandeur = $this;

        return [
            'id' => $demandeur->id,
            'type_demandeur' => $this->type_demandeur,
            'statut' => $this->statut,
            'motif_rejet' => $this->motif_rejet,
            'valider_a' => $this->valider_a,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->when($this->deleted_at, $this->deleted_at),
            'nom' => $demandeur->user->client->nom ?? null,
            'raison_sociale' => $demandeur->user->entreprise->raison_sociale ?? null,
            'prenoms' => $demandeur->user->client->prenoms ?? null,
            'date_naissance' => $demandeur->user->client->date_naissance ?? null,
            'sexe' => $demandeur->user->client->sexe ?? null,
            'profession' => $demandeur->user->client->profession ?? null,
            'email' => $demandeur->user->email,
            'contact' => $demandeur->user->contact,
            'reponses_questionnaire' => $this->when('reponsesQuestionnaire', function () {
                return $this->reponsesQuestionnaire->map(function ($reponse) {
                    return [
                        'id' => $reponse->id,
                        'reponse_text' => $reponse->reponse_text,
                        'reponse_bool' => $reponse->reponse_bool,
                        'reponse_date' => $reponse->reponse_date,
                        'reponse_fichier' => $reponse->reponse_fichier,
                        'question_id' => $reponse->question_id,
                        'created_at' => $reponse->created_at,
                        'updated_at' => $reponse->updated_at, 
                        'deleted_at' => $reponse->deleted_at,
                        'question' => $reponse->when('question', function () use ($reponse) {
                            return [
                                'id' => $reponse->question->id,
                                'libelle' => $reponse->question->libelle,
                                'type_donnees' => $reponse->question->type_donnees,
                                'obligatoire' => $reponse->question->obligatoire
                            ];
                        })
                    ];
                });
            }),
        ];
    }
}