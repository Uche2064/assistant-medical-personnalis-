<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SinistreResource extends JsonResource
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
            'description' => $this->description,
            'date_sinistre' => $this->date_sinistre,
            'statut' => $this->statut,
            'statut_libelle' => $this->statut->getLabel(),
            'assure' => [
                'id' => $this->assure->id,
                'nom' => $this->assure->nom,
                'prenoms' => $this->assure->prenoms,
                'email' => $this->assure->email,
                'contact' => $this->assure->contact,
                'est_principal' => $this->assure->est_principal,
                'type_assure' => $this->getTypeAssure(),
                'contrat' => $this->when($this->assure->contrat, [
                    'id' => $this->assure->contrat->id ?? null,
                    'libelle' => $this->assure->contrat->libelle ?? null,
                    'est_actif' => $this->assure->contrat->est_actif ?? false,
                ]),
            ],
            'prestataire' => [
                'id' => $this->prestataire->id,
                'raison_sociale' => $this->prestataire->raison_sociale,
                'type_prestataire' => $this->prestataire->type_prestataire,
            ],
            'factures' => FactureResource::collection($this->whenLoaded('factures')),
            'total_amount_claimed' => $this->total_amount_claimed,
            'total_amount_to_reimburse' => $this->total_amount_to_reimburse,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Déterminer le type d'assuré
     */
    private function getTypeAssure()
    {
        if ($this->assure->entreprise_id) {
            return 'Employé - ' . ($this->assure->entreprise->raison_sociale ?? 'Entreprise');
        } elseif ($this->assure->est_principal) {
            return 'Assuré Principal';
        } elseif ($this->assure->assure_principal_id) {
            return 'Bénéficiaire';
        }
        
        return 'Inconnu';
    }
}