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
        $nom = $demandeur->user->personne->nom ?? null;
        $prenoms = $demandeur->user->personne->prenoms ?? null;
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
            'demandeur' => $demandeurLabel,
            'date_naissance' => $demandeur->user->personne->date_naissance ?? null,
            'sexe' => $demandeur->user->personne->sexe ?? null,
            'profession' => $demandeur->user->personne->profession ?? null,
            'email' => $demandeur->user->email,
            'contact' => $demandeur->user->contact,
            'statut' => $this->statut,
            'motif_rejet' => $this->motif_rejet,
            'valider_a' => $this->valider_a,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'reponses_questions' => $this->whenLoaded('reponsesQuestions', function () {
                return $this->reponsesQuestions->map(function ($reponse) {
                    return [
                        'id' => $reponse->id,
                        'reponse' => $reponse->reponse,
                        'question' => $reponse->relationLoaded('question') ? [
                                        'id' => $reponse->question->id,
                                        'libelle' => $reponse->question->libelle,
                                        'type_de_donnee' => $reponse->question->type_de_donnee,
                                        'est_obligatoire' => $reponse->question->est_obligatoire
                                    ] : null
                    ];
                });
            }),

            // Liste des bénéficiaires s'il y en a
            'beneficiaires' => $this->whenLoaded('beneficiaires', function () {
                return $this->beneficiaires->map(function ($beneficiaire) {
                    return [
                        'id' => $beneficiaire->id,
                        'nom' => $beneficiaire->nom,
                        'prenoms' => $beneficiaire->prenoms,
                        'date_naissance' => $beneficiaire->date_naissance,
                        'sexe' => $beneficiaire->sexe,
                        'profession' => $beneficiaire->profession,
                        'lien_parente' => $beneficiaire->lien_parente,
                        'email' => $beneficiaire->user->email ?? null,
                        'contact' => $beneficiaire->user->contact ?? null,
                        'photo' => $beneficiaire->photo,
                        'reponses_questions' => $this->whenLoaded('reponsesQuestions', function () {
                            return $this->reponsesQuestions->map(function ($reponse) {
                                return [
                                    'id' => $reponse->id,
                                    'reponse' => $reponse->reponse,
                                    'question' => $reponse->relationLoaded('question') ? [
                                        'id' => $reponse->question->id,
                                        'libelle' => $reponse->question->libelle,
                                        'type_de_donnee' => $reponse->question->type_de_donnee,
                                        'est_obligatoire' => $reponse->question->est_obligatoire
                                    ] : null
                                ];
                            });
                        }),
                    ];
                });
            }),
        ];
    }
}
