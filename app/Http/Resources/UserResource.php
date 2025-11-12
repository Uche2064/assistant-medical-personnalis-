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
            'code_parrainage' => $this->code_parrainage,
            'updated_at' => $this->updated_at,
            'solde' => $this->solde ?? 0,
            'photo_url' => $this->photo_url ?? null,
        ];

        // Informations personne (pour clients et prestataires)
        if ($this->whenLoaded('personne') && $this->personne) {
            $userData['personne_id'] = $this->personne->id;
            $userData['nom'] = $this->personne->nom;
            $userData['prenoms'] = $this->personne->prenoms;
            $userData['sexe'] = $this->personne->sexe;
            $userData['date_naissance'] = $this->personne->date_naissance;
            $userData['profession'] = $this->personne->profession;
            $userData['gestionnaire_id'] = $this->personne->gestionnaire_id;
        }

        // Informations personnel (si c'est un membre du personnel)
        if ($this->whenLoaded('personnel') && $this->personnel) {
            $userData['personnel_id'] = $this->personnel->id;
            $userData['nom'] = $this->personnel->nom;
            $userData['prenoms'] = $this->personnel->prenoms;
            $userData['fonction'] = $this->personnel->fonction ?? null;
        }

        // Informations client (si c'est un client)
        if ($this->whenLoaded('client') && $this->client) {
            $userData['client_id'] = $this->client->id;
            $userData['type_client'] = $this->client->type_client;
        }

        // Informations prestataire (si c'est un prestataire)
        if ($this->whenLoaded('prestataire') && $this->prestataire) {
            $userData['prestataire_id'] = $this->prestataire->id;
            $userData['type_prestataire'] = $this->prestataire->type_prestataire;
            $userData['nom_etablissement'] = $this->prestataire->nom ?? null;
        }

        // Informations assure (si applicable)
        if ($this->whenLoaded('assure') && $this->assure) {
            $userData['assure_id'] = $this->assure->id;
            $userData['est_principal'] = $this->assure->est_principal;
        }

        return $userData;
    }
}
