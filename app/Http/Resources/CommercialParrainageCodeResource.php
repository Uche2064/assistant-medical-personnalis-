<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommercialParrainageCodeResource extends JsonResource
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
            'code_parrainage' => $this->code_parrainage,
            'date_debut' => $this->date_debut->format('Y-m-d H:i:s'),
            'date_expiration' => $this->date_expiration->format('Y-m-d H:i:s'),
            'est_actif' => $this->est_actif,
            'est_renouvele' => $this->est_renouvele,
            'est_expire' => $this->isExpired(),
            'peut_renouveler' => $this->canBeRenewed(),
            'jours_restants' => now()->diffInDays($this->date_expiration, false),
            'duree_totale' => $this->date_debut->diffInDays($this->date_expiration),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            
            // Informations formatées pour l'affichage
            'date_debut_formatee' => $this->date_debut->format('d/m/Y à H:i'),
            'date_expiration_formatee' => $this->date_expiration->format('d/m/Y à H:i'),
            'statut' => $this->getStatut(),
            'statut_color' => $this->getStatutColor()
        ];
    }

    /**
     * Obtenir le statut du code
     */
    private function getStatut(): string
    {
        if ($this->est_renouvele) {
            return 'Renouvelé';
        }
        
        if ($this->isExpired()) {
            return 'Expiré';
        }
        
        if ($this->est_actif) {
            return 'Actif';
        }
        
        return 'Inactif';
    }

    /**
     * Obtenir la couleur du statut
     */
    private function getStatutColor(): string
    {
        if ($this->est_renouvele) {
            return 'warning';
        }
        
        if ($this->isExpired()) {
            return 'danger';
        }
        
        if ($this->est_actif) {
            return 'success';
        }
        
        return 'secondary';
    }
}