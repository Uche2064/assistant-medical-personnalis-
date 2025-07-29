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
            'prime_proposee' => $this->prime_proposee,
            'taux_couverture' => $this->taux_couverture,
            'frais_gestion' => $this->frais_gestion,
            'statut' => $this->statut,
            'commentaires_technicien' => $this->commentaires_technicien,
            'date_proposition' => $this->date_proposition,
            'date_acceptation' => $this->date_acceptation,
            'date_signature' => $this->date_signature,
            
            // Informations du client
            'client' => [
                'id' => $this->user->id,
                'email' => $this->user->email,
                'contact' => $this->user->contact,
                'adresse' => $this->user->adresse,
            ],
            
            // Garanties incluses
            'garanties' => $this->garanties->map(function ($garantie) {
                return [
                    'id' => $garantie->id,
                    'nom' => $garantie->nom,
                    'description' => $garantie->description,
                    'taux_couverture' => $garantie->taux_couverture,
                    'categorie' => $garantie->categorieGarantie->nom ?? null,
                ];
            }),
            
            // Informations de validation
            'valide_par' => $this->validePar ? [
                'id' => $this->validePar->id,
                'nom' => $this->validePar->nom,
                'prenoms' => $this->validePar->prenoms,
                'role' => $this->validePar->role,
            ] : null,
            
            // Token d'acceptation (si applicable)
            'token_acceptation' => $this->when($this->statut === 'propose', function () {
                return Cache::get("contrat_acceptation_{$this->id}");
            }),
            
            // Dates
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 