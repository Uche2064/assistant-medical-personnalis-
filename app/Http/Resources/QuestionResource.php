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
            'type_donnee' => $this->type_donnee,
            'destinataire' => $this->destinataire,
            'obligatoire' => (bool) $this->obligatoire,
            'est_actif' => (bool) $this->est_actif,
            'options' => is_string($this->options) ? json_decode($this->options, true) : $this->options,
            'cree_par_id' => $this->cree_par_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 