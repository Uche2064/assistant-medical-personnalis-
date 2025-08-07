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
            'libelle' => $this->libelle,
            'libelle_formatted' => ucfirst($this->libelle),
            'description' => $this->description,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            
            // Relations
            'garanties' => $this->whenLoaded('garanties', function () {
                return $this->garanties->map(function ($garantie) {
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
                        'created_at' => $garantie->created_at?->format('Y-m-d H:i:s'),
                    ];
                });
            }),
            
            'medecin_controleur' => $this->whenLoaded('medecinControleur', function () {
                return [
                    'id' => $this->medecinControleur->id,
                    'nom' => $this->medecinControleur->nom,
                    'prenoms' => $this->medecinControleur->prenoms,
                    'nom_complet' => $this->medecinControleur->nom . ' ' . $this->medecinControleur->prenoms,
                    'email' => $this->medecinControleur->user->email ?? null,
                    'telephone' => $this->medecinControleur->telephone,
                ];
            }),
            
            // Statistiques
            'statistiques' => $this->whenLoaded('garanties', function () {
                return [
                    'nombre_garanties' => $this->garanties->count(),
                    'plafond_moyen' => $this->garanties->avg('plafond'),
                    'prix_moyen' => $this->garanties->avg('prix_standard'),
                    'taux_couverture_moyen' => $this->garanties->avg('taux_couverture'),
                ];
            }),
        ];
    }
}
