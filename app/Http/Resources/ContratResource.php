<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Cache;

class ContratResource extends JsonResource
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
            'type_contrat' => $this->type_contrat,
            'prime_standard' => $this->prime_standard,
            'est_actif' => $this->est_actif,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Informations du technicien
            'technicien' => $this->whenLoaded('technicien', function () {
                return [
                    'id' => $this->technicien->id,
                    'nom' => $this->technicien->nom,
                    'prenoms' => $this->technicien->prenoms,
                ];
            }),
            
            // Catégories de garanties avec taux de couverture
            'categories_garanties' => $this->whenLoaded('categoriesGaranties', function () {
                return $this->categoriesGaranties->map(function ($categorie) {
                    return [
                        'id' => $categorie->id,
                        'libelle' => $categorie->libelle,
                        'description' => $categorie->description,
                        'couverture' => $categorie->pivot->couverture,
                        'garanties' => $categorie->garanties->map(function ($garantie) {
                            return [
                                'id' => $garantie->id,
                                'libelle' => $garantie->libelle,
                                'plafond' => $garantie->plafond,
                                'prix_standard' => $garantie->prix_standard,
                                'taux_couverture' => $garantie->taux_couverture,
                            ];
                        }),
                    ];
                });
            }),
        ];
    }
} 