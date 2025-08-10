<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DemandeAdhesionEntrepriseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'entreprise' => [
                'id' => $this->entreprise->id ?? null,
                'raison_sociale' => $this->entreprise->raison_sociale ?? null,
                'email' => $this->entreprise->user->email ?? null,
                'contact' => $this->entreprise->user->contact ?? null,
                'adresse' => $this->entreprise->user->adresse ?? null,
            ],
            'statut' => $this->statut,
            'employes' => $this->employes->map(function ($employe) {
                return [
                    'id' => $employe->id,
                    'nom' => $employe->nom,
                    'prenoms' => $employe->prenoms,
                    'email' => $employe->user->email ?? null,
                    'fiche_medicale' => $employe->fiche_medicale ?? null,
                    'statut' => $employe->statut ?? null,
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 