<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
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
        'libelle' => $this->libelle,
        'type_de_donnee' => $this->type_de_donnee,
        'destinataire' => $this->destinataire,
        'est_obligatoire' => (bool) $this->est_obligatoire,
        'est_active' => (bool) $this->est_active,
        'options' => is_string($this->options) ? json_decode($this->options, true) : $this->options,
        'cree_par' => $this->creeePar
            ? [
                'nom' => $this->creeePar->user?->personne?->nom,
                'prenoms' => $this->creeePar->user?->personne?->prenoms,
            ]
            : null,
        'created_at' => $this->created_at,
        'updated_at' => $this->updated_at,
    ];
}

} 