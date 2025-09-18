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
            'description' => $this->description,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            // Relations
            'garanties' => $this->whenLoaded('garanties', function () {
                return $this->garanties->map(function ($garantie) {
                    return [
                        'id' => $garantie->id,
                        'libelle' => $garantie->libelle,
                        'plafond' => $garantie->plafond,
                        'prix_standard' => $garantie->prix_standard,
                        'taux_couverture' => $garantie->taux_couverture,
                        'est_active' => $garantie->est_active
                    ];
                });
            }),
            
            'medecin_controleur' => $this->whenLoaded('medecinControleur', function () {
                return [
                    'nom' => $this->medecinControleur->user->personne->nom,
                    'prenoms' => $this->medecinControleur->user->personne->prenoms,
                ];
            }),
            // Statistiques
            // 'statistiques' => $this->whenLoaded('garanties', function () {
            //     return [
            //         'nombre_garanties' => $this->garanties->count(),
            //         'plafond_moyen' => $this->garanties->avg('plafond'),
            //         'prix_moyen' => $this->garanties->avg('prix_standard'),
            //         'taux_couverture_moyen' => $this->garanties->avg('taux_couverture'),
            //     ];
            // }),
        ];
    }
}
