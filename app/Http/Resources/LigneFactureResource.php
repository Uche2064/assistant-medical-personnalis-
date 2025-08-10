<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LigneFactureResource extends JsonResource
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
            'libelle_acte' => $this->libelle_acte,
            'prix_unitaire' => $this->prix_unitaire,
            'quantite' => $this->quantite,
            'prix_total' => $this->prix_total,
            'taux_couverture' => $this->taux_couverture,
            'montant_couvert' => $this->montant_couvert,
            'ticket_moderateur' => $this->ticket_moderateur,
            'garantie' => [
                'id' => $this->garantie->id,
                'libelle' => $this->garantie->libelle,
                'prix_standard' => $this->garantie->prix_standard,
                'plafond' => $this->garantie->plafond,
                'categorie_garantie' => [
                    'id' => $this->garantie->categorieGarantie->id,
                    'libelle' => $this->garantie->categorieGarantie->libelle,
                ],
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}