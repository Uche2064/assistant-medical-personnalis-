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
                // Filtrer uniquement les réponses de l'assuré principal
                $reponsesAssurePrincipal = $this->reponsesQuestions->where('user_id', $this->user_id);

                return $reponsesAssurePrincipal->map(function ($reponse) {
                    return [
                        'id' => $reponse->id,
                        'reponse' => $reponse->reponse,
                        'date_reponse' => $reponse->date_reponse,
                        'user_id' => $reponse->user_id,
                        'question' => $reponse->relationLoaded('question') ? [
                            'id' => $reponse->question->id,
                            'libelle' => $reponse->question->libelle,
                            'type_de_donnee' => $reponse->question->type_de_donnee,
                            'obligatoire' => $reponse->question->obligatoire
                        ] : null
                    ];
                })->values();
            }),

            // // Liste des bénéficiaires s'il y en a
            'beneficiaires' => $this->when($this->assurePrincipal && $this->assurePrincipal->relationLoaded('beneficiaires'), function () {
                return $this->assurePrincipal->beneficiaires->map(function ($beneficiaire) {
                    // Récupérer les réponses de ce bénéficiaire
                    $reponsesBeneficiaire = $this->reponsesQuestions->where('user_id', $beneficiaire->user_id);

                    return [
                        'id' => $beneficiaire->id,
                        'nom' => $beneficiaire->user->personne->nom ?? null,
                        'prenoms' => $beneficiaire->user->personne->prenoms ?? null,
                        'date_naissance' => $beneficiaire->user->personne->date_naissance ?? null,
                        'sexe' => $beneficiaire->user->personne->sexe ?? null,
                        'profession' => $beneficiaire->user->personne->profession ?? null,
                        'lien_parente' => $beneficiaire->lien_parente,
                        'email' => $beneficiaire->user->email ?? null,
                        'contact' => $beneficiaire->user->contact ?? null,
                        'photo_url' => $beneficiaire->user->photo_url ?? null,
                        'est_principal' => $beneficiaire->est_principal,
                        'created_at' => $beneficiaire->created_at,
                        'reponses_questions' => $reponsesBeneficiaire->map(function ($reponse) {
                            return [
                                'id' => $reponse->id,
                                'reponse' => $reponse->reponse,
                                'date_reponse' => $reponse->date_reponse,
                                'user_id' => $reponse->user_id,
                                'question' => $reponse->relationLoaded('question') ? [
                                    'id' => $reponse->question->id,
                                    'libelle' => $reponse->question->libelle,
                                    'type_de_donnee' => $reponse->question->type_de_donnee,
                                    'obligatoire' => $reponse->question->obligatoire
                                ] : null
                            ];
                        })->values()
                    ];
                });
            }),
        ];
    }
}
