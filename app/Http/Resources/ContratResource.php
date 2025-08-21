<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'libelle' => $this->libelle,
            'prime_standard' => $this->prime_standard,
            'prime_standard_formatted' => number_format($this->prime_standard, 0, ',', ' ') . ' FCFA',
            'est_actif' => $this->est_actif,
            'categories_garanties_standard' => $this->categories_garanties_standard,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'technicien' => $this->whenLoaded('technicien', function () {
                return [
                    'id' => $this->technicien->id,
                    'nom_complet' => $this->technicien->nom . ' ' . $this->technicien->prenom,
                    'email' => $this->technicien->user->email,
                ];
            }),
            'assures' => $this->whenLoaded('assures', function () {
                return $this->assures->map(function ($assure) {
                    return [
                        'id' => $assure->id,
                        'nom_complet' => $assure->nom . ' ' . $assure->prenoms,
                        'contact' => $assure->contact
                    ];
                });
            }),
            'categories_garanties' => $this->whenLoaded('categoriesGaranties', function () {
                return $this->categoriesGaranties->map(function ($categorie) {
                    return [
                        'id' => $categorie->id,
                        'libelle' => $categorie->libelle,
                        'libelle_formatted' => ucfirst($categorie->libelle),
                        'description' => $categorie->description,
                        'couverture' => $categorie->pivot->couverture ?? null,
                        'couverture_formatted' => ($categorie->pivot->couverture ?? 0) . '%',
                        'garanties' => $categorie->garanties ? $categorie->garanties->map(function ($garantie) {
                            return [
                                'id' => $garantie->id,
                                'libelle' => $garantie->libelle,
                                'libelle_formatted' => ucfirst($garantie->libelle),
                                'plafond' => $garantie->plafond,
                                'plafond_formatted' => number_format($garantie->plafond, 0, ',', ' ') . ' FCFA',
                                'prix_standard' => $garantie->prix_standard,
                                'prix_standard_formatted' => number_format($garantie->prix_standard, 0, ',', ' ') . ' FCFA',
                                'taux_couverture' => $garantie->taux_couverture,
                                'taux_couverture_formatted' => $garantie->taux_couverture . '%',
                            ];
                        }) : [],
                    ];
                });
            }),

            // Statistiques calculées
            'statistiques' => $this->whenLoaded('categoriesGaranties', function () {
                return [
                    'nombre_categories' => $this->categoriesGaranties->count(),
                    'nombre_garanties' => $this->categoriesGaranties->sum(function ($categorie) {
                        return $categorie->garanties ? $categorie->garanties->count() : 0;
                    }),
                    'couverture_moyenne' => $this->categoriesGaranties->pluck('pivot.couverture')->filter()->avg() ?? 0,
                ];
            }),


        ];
    }
}
