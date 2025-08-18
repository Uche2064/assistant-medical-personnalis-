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
            
        ];
    }
}
