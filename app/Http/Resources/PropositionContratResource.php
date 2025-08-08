<?php

namespace App\Http\Resources;

use App\Enums\StatutPropositionContratEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropositionContratResource extends JsonResource
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
            'statut' => $this->statut,
            'statut_label' => $this->statut?->value ?? $this->statut,
            'commentaires_technicien' => $this->commentaires_technicien,
            'date_proposition' => $this->date_proposition?->format('Y-m-d H:i:s'),
            'date_acceptation' => $this->date_acceptation?->format('Y-m-d H:i:s'),
            'date_refus' => $this->date_refus?->format('Y-m-d H:i:s'),
            
            // Données du contrat
            'contrat' => $this->whenLoaded('contrat', function () {
                return [
                    'id' => $this->contrat->id,
                    'type_contrat' => $this->contrat->type_contrat,
                    'type_contrat_label' => $this->contrat->type_contrat?->getLabel() ?? 'Non défini',
                    'prime_standard' => $this->contrat->prime_standard,
                    'prime_standard_formatted' => number_format($this->contrat->prime_standard, 0, ',', ' ') . ' FCFA',
                    'est_actif' => $this->contrat->est_actif,
                ];
            }),
            
            // Données calculées
            'prime' => $this->prime,
            'prime_formatted' => $this->prime_formatted,
            'taux_couverture' => $this->taux_couverture,
            'frais_gestion' => $this->frais_gestion,
            'prime_totale' => $this->prime_totale,
            'prime_totale_formatted' => $this->prime_totale_formatted,
            
            // Relations
            'demande_adhesion' => $this->whenLoaded('demandeAdhesion', function () {
                return [
                    'id' => $this->demandeAdhesion->id,
                    'type_demandeur' => $this->demandeAdhesion->type_demandeur,
                    'statut' => $this->demandeAdhesion->statut,
                    'user' => $this->demandeAdhesion->user ? [
                        'id' => $this->demandeAdhesion->user->id,
                        'nom' => $this->demandeAdhesion->user->nom,
                        'prenoms' => $this->demandeAdhesion->user->prenoms,
                        'email' => $this->demandeAdhesion->user->email,
                        'nom_complet' => $this->demandeAdhesion->user->nom . ' ' . $this->demandeAdhesion->user->prenoms,
                    ] : null,
                ];
            }),
            
            'technicien' => $this->whenLoaded('technicien', function () {
                return [
                    'id' => $this->technicien->id,
                    'nom' => $this->technicien->nom,
                    'prenoms' => $this->technicien->prenoms,
                    'nom_complet' => $this->technicien->nom . ' ' . $this->technicien->prenoms,
                    'email' => $this->technicien->user->email ?? null,
                    'telephone' => $this->technicien->telephone,
                ];
            }),
            
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
                    ];
                });
            }),
            
            // Métadonnées
            'meta' => [
                'is_proposee' => $this->isProposee(),
                'can_be_accepted' => $this->statut === \App\Enums\StatutPropositionContratEnum::PROPOSEE,
                'can_be_refused' => $this->statut === \App\Enums\StatutPropositionContratEnum::PROPOSEE,
                'days_until_expiry' => $this->date_proposition ? max(0, now()->diffInDays($this->date_proposition->addDays(7), false)) : null,
            ],
        ];
    }
}
