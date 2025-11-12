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
            'nom' => $this->user->personne->nom ?? null,
            'prenoms' => $this->user->personne->prenoms ?? null,
            'date_naissance' => $this->user->personne->date_naissance ?? null,
            'sexe' => $this->user->personne->sexe ?? null,
            'lien_parente' => $this->lien_parente,
            'profession' => $this->user->personne->profession ?? null,
            'photo_url' => $this->user->photo_url ?? null,
            'contact' => $this->user->contact ?? null,
            'email' => $this->user->email ?? null,
            'adresse' => $this->user->adresse ?? null,
            'est_principal' => $this->est_principal,
            'assure_principal_id' => $this->assure_principal_id,
            'assure_principal' => $this->whenLoaded('assurePrincipal', function () {
                return [
                    'id' => $this->assurePrincipal->id,
                    'nom' => $this->assurePrincipal->user->personne->nom ?? null,
                    'prenoms' => $this->assurePrincipal->user->personne->prenoms ?? null,
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
