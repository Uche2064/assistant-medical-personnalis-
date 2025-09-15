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

        // Construire le nom complet si disponible
        $nom = $demandeur->user->assure->nom ?? null;
        $prenoms = $demandeur->user->assure->prenoms ?? null;
        $fullName = trim(($nom ?? '') . ' ' . ($prenoms ?? ''));

        if ($fullName === '') {
            $fullName = null; // si aucun des deux n’est fourni
        }

        // Déterminer le champ "demandeur"
        $demandeurLabel = $demandeur->user->entreprise->raison_sociale
            ?? $demandeur->user->prestataire->raison_sociale
            ?? $fullName;

        return [
            'id' => $demandeur->id,
            'type_demandeur' => $this->type_demandeur,
            'statut' => $this->statut,
            'motif_rejet' => $this->motif_rejet,
            'valider_a' => $this->valider_a,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->when($this->deleted_at, $this->deleted_at),

            // 👇 Ici c'est corrigé
            'demandeur' => $demandeurLabel,

            'date_naissance' => $demandeur->user->assure->date_naissance ?? null,
            'sexe' => $demandeur->user->assure->sexe ?? null,
            'profession' => $demandeur->user->assure->profession ?? null,
            'email' => $demandeur->user->email,
            'contact' => $demandeur->user->contact,

            'reponses_questionnaire' => $this->whenLoaded('reponsesQuestionnaire', function () {
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
                        'question' => $reponse->whenLoaded('question', function () use ($reponse) {
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
