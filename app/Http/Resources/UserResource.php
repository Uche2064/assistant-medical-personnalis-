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
            'nom' => null,
            'prenoms' => null,
            'role' => $this->roles[0]->name,
            'adresse' => $this->adresse,
            'photo_url' => $this->photo_url,
            'est_actif' => $this->est_actif,
            'mot_de_passe_a_changer' => $this->mot_de_passe_a_changer,
            'sexe' => null,
            'date_naissance' => null,
            'type_demandeur' => null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        if ($this->whenLoaded('personnel') && $this->personnel) {
            $userData['nom'] = $this->personnel->nom;
            $userData['prenoms'] = $this->personnel->prenoms;
            $userData['sexe'] = $this->personnel->sexe;
            $userData['date_naissance'] = $this->personnel->date_naissance;
            $userData['code_parainage'] = $this->personnel->code_parainage;
            $userData['gestionnaire_id'] = $this->personnel->gestionnaire_id;
        } elseif ($this->whenLoaded('client') && $this->client) {
            $userData['nom'] = $this->client->nom ?? null;
            $userData['prenoms'] = $this->client->prenoms ?? null;
            $userData['raison_sociale'] = $this->client->raison_sociale ?? null;
            $userData['type_demandeur'] = $this->client->type_client ?? null;
            $userData['sexe'] = $this->client->sexe ?? null;
            $userData['date_naissance'] = $this->client->date_naissance ?? null;
        } elseif ($this->whenLoaded('entreprise') && $this->entreprise) {
            $userData['nom'] = $this->entreprise->nom ?? null;
            $userData['prenoms'] = $this->entreprise->prenoms ?? null;
            $userData['raison_sociale'] = $this->entreprise->raison_sociale ?? null;
            $userData['type_demandeur'] = $this->entreprise->type_personne ?? null;
            $userData['sexe'] = $this->entreprise->sexe ?? null;
            $userData['date_naissance'] = $this->entreprise->date_naissance ?? null;
        } elseif ($this->whenLoaded('prestataire') && $this->prestataire) {
            $userData['nom'] = $this->prestataire->nom ?? null;
            $userData['prenoms'] = $this->prestataire->prenoms ?? null;
            $userData['raison_sociale'] = $this->prestataire->raison_sociale ?? null;
            $userData['type_demandeur'] = $this->prestataire->type_prestataire ?? null;
            $userData['sexe'] = $this->prestataire->sexe ?? null;
            $userData['date_naissance'] = $this->prestataire->date_naissance ?? null;
        }

        return $userData;
    }
}
