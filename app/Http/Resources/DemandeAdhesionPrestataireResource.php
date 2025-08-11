<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DemandeAdhesionPrestataireResource extends JsonResource
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
            'statut' => $this->statut,
            'type_demandeur' => $this->type_demandeur,
            'date_creation' => $this->created_at,
            'date_modification' => $this->updated_at,
            
            // Informations du prestataire
            'prestataire' => [
                'id' => $this->prestataire->id ?? null,
                'raison_sociale' => $this->prestataire->raison_sociale ?? null,
                'email' => $this->prestataire->email ?? null,
                'contact' => $this->prestataire->contact ?? null,
                'adresse' => $this->prestataire->adresse ?? null,
                'type_prestataire' => $this->prestataire->type_prestataire ?? null,
                'statut_prestataire' => $this->prestataire->statut ?? null,
            ],
            
            // Réponses au questionnaire
            'reponses' => $this->reponsesQuestionnaire->map(function ($reponse) {
                return [
                    'id' => $reponse->id,
                    'question_id' => $reponse->question_id,
                    'valeur' => $reponse->valeur,
                    'fichier_url' => $reponse->fichier_url,
                    'date_reponse' => $reponse->created_at,
                ];
            }),
            
            // Informations de traitement
            'traitement' => [
                'traite_par' => $this->traite_par ? [
                    'id' => $this->traite_par->id,
                    'nom' => $this->traite_par->nom,
                    'prenoms' => $this->traite_par->prenoms,
                    'role' => $this->traite_par->role,
                ] : null,
                'date_traitement' => $this->date_traitement,
                'commentaires' => $this->commentaires,
            ],
        
        ];
    }
} 