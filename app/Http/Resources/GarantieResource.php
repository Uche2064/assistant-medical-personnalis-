<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GarantieResource extends JsonResource
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
            'libelle' => $this->libelle,
            'categorie_garantie_id' => $this->categorie_garantie_id,
            'medecin_controleur_id' => $this->medecin_controleur_id,
            'plafond' => $this->plafond,
            'prix_standard' => $this->prix_standard,
            'taux_couverture' => $this->taux_couverture,
        ];
    }
}
