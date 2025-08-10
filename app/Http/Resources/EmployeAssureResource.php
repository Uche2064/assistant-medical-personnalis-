<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeAssureResource extends JsonResource
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
            'contact' => $this->contact,
            'email' => $this->email,
            'photo' => $this->photo,
            'beneficiaires' => $this->beneficiaires->map(function ($beneficiaire) {
                return [
                    'id' => $beneficiaire->id,
                    'nom' => $beneficiaire->nom,
                    'prenoms' => $beneficiaire->prenoms,
                    'date_naissance' => $beneficiaire->date_naissance,
                    'sexe' => $beneficiaire->sexe,
                    'contact' => $beneficiaire->contact,
                    'photo' => $beneficiaire->photo,
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 