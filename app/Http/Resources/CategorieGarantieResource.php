<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategorieGarantieResource extends JsonResource
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
            'description' => $this->description,
            'medecin_controleur_id' => $this->medecin_controleur_id,
            'garanties' => GarantieResource::collection($this->garanties),
        ];
    }
}
