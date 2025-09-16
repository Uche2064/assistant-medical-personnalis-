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
            'libelle' => ucfirst($this->libelle),
            'plafond' => $this->plafond,
            'prix_standard' => $this->prix_standard,
            'taux_couverture' => $this->taux_couverture,
            'est_active' => $this->est_active,
            'categorie_garantie' => [
                'id' => $this->categorieGarantie->id,
                'libelle' => ucfirst($this->categorieGarantie->libelle),
            ],
            'medecin_controleur' => [
                'nom' => $this->medecinControleur->user->personne->nom,
                'prenoms' => $this->medecinControleur->user->personne->prenoms ?? '',
            ],
        ];
    }
}
