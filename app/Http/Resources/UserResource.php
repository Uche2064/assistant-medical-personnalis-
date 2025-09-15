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
            $userData['photo'] = $this->photo ?? null;
        } else if ($this->whenLoaded('assure') && $this->assure) {
            $userData['nom'] = $this->assure->nom ?? null;
            $userData['prenoms'] = $this->assure->prenoms ?? null;
            $userData['type_demandeur'] = 'physique';
            $userData['sexe'] = $this->assure->sexe ?? null;
            $userData['date_naissance'] = $this->assure->date_naissance ?? null;
            $userData['profession'] = $this->assure->profession ?? null;
            $userData['photo'] = $this->photo ?? null;
        } else if ($this->whenLoaded('entreprise') && $this->entreprise) {
            $userData['raison_sociale'] = $this->entreprise->raison_sociale ?? null;
            $userData['type_demandeur'] = 'entreprise';
        } else if ($this->whenLoaded('prestataire') && $this->prestataire) {
            $userData['raison_sociale'] = $this->prestataire->raison_sociale ?? null;
            $userData['type_demandeur'] = $this->prestataire->type_prestataire ?? null;
        }

        return $userData;
    }
}
