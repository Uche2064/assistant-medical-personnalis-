<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $userData = [
            'id' => $this->id,
            'email' => $this->email,
            'contact' => $this->contact,
            'role' => $this->roles && count($this->roles) > 0 ? $this->roles[0]->name : null,
            'adresse' => $this->adresse,
            'est_actif' => $this->est_actif,
            'mot_de_passe_a_changer' => $this->mot_de_passe_a_changer,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'solde' => $this->solde ?? 0,
        ];

        if ($this->whenLoaded('personne') && $this->personne) {
            $userData['nom'] = $this->personne->nom;
            $userData['prenoms'] = $this->personne->prenoms;
            $userData['sexe'] = $this->personne->sexe;
            $userData['date_naissance'] = $this->personne->date_naissance;
            $userData['gestionnaire_id'] = $this->personne->gestionnaire_id;
            $userData['photo_url'] = $this->photo_url ?? null;
        }

        return $userData;
    }
}
