<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientContratResource extends JsonResource
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
            'user_id' => $this->user_id,
            'contrat_id' => $this->contrat_id,
            'type_client' => $this->type_client,
            'date_debut' => $this->date_debut?->format('Y-m-d'),
            'date_debut_formatted' => $this->date_debut?->format('d/m/Y'),
            'date_fin' => $this->date_fin?->format('Y-m-d'),
            'date_fin_formatted' => $this->date_fin?->format('d/m/Y'),
            'statut' => $this->statut,
            'statut_label' => $this->statut?->getLabel() ?? $this->statut,
            'numero_police' => $this->numero_police,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            
            // Données calculées
            'jours_restants' => $this->date_fin ? max(0, now()->diffInDays($this->date_fin, false)) : 0,
            'jours_restants_formatted' => $this->date_fin ? $this->formatJoursRestants($this->date_fin) : 'N/A',
            
            // Relation avec le contrat
            'contrat' => $this->whenLoaded('contrat', function () {
                return [
                    'id' => $this->contrat->id,
                    'libelle' => $this->contrat->libelle,
                    'prime_standard' => $this->contrat->prime_standard,
                    'prime_standard_formatted' => number_format($this->contrat->prime_standard, 0, ',', ' ') . ' FCFA',
                    'prime_totale' => $this->contrat->prime_totale,
                    'prime_totale_formatted' => number_format($this->contrat->prime_totale, 0, ',', ' ') . ' FCFA',
                    'frais_gestion' => $this->contrat->frais_gestion,
                    'frais_gestion_formatted' => number_format($this->contrat->frais_gestion, 0, ',', ' ') . ' FCFA',
                    'couverture' => $this->contrat->couverture,
                    'couverture_formatted' => ($this->contrat->couverture ?? 0) . '%',
                    'est_actif' => $this->contrat->est_actif,
                    'categories_garanties' => $this->whenLoaded('contrat.categoriesGaranties', function () {
                        return $this->contrat->categoriesGaranties->map(function ($categorie) {
                            return [
                                'id' => $categorie->id,
                                'libelle' => $categorie->libelle,
                                'libelle_formatted' => ucfirst($categorie->libelle),
                                'description' => $categorie->description,
                                'couverture' => $categorie->pivot->couverture ?? null,
                                'couverture_formatted' => ($categorie->pivot->couverture ?? 0) . '%',
                                'garanties' => $this->whenLoaded('contrat.categoriesGaranties.garanties', function () use ($categorie) {
                                    return $categorie->garanties ? $categorie->garanties->map(function ($garantie) {
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
                                    }) : [];
                                }),
                            ];
                        });
                    }),
                ];
            }),
            
            // Relation avec le client
            'client' => $this->whenLoaded('client', function () {
                return [
                    'id' => $this->client->id,
                    'email' => $this->client->email,
                    'contact' => $this->client->contact,
                    'adresse' => $this->client->adresse,
                    'assure' => $this->whenLoaded('client.assure', function () {
                        return [
                            'id' => $this->client->assure->id,
                            'nom' => $this->client->assure->nom,
                            'prenoms' => $this->client->assure->prenoms,
                            'nom_complet' => $this->client->assure->nom_complet,
                            'date_naissance' => $this->client->assure->date_naissance?->format('Y-m-d'),
                            'date_naissance_formatted' => $this->client->assure->date_naissance?->format('d/m/Y'),
                            'sexe' => $this->client->assure->sexe,
                            'sexe_label' => $this->client->assure->sexe?->getLabel() ?? 'Non défini',
                            'profession' => $this->client->assure->profession,
                            'est_principal' => $this->client->assure->est_principal,
                        ];
                    }),
                ];
            }),
            
            // Relation avec les prestataires assignés
            'prestataires' => $this->whenLoaded('prestataires', function () {
                return $this->prestataires->map(function ($clientPrestataire) {
                    return [
                        'id' => $clientPrestataire->id,
                        'statut' => $clientPrestataire->statut,
                        'statut_label' => $clientPrestataire->statut?->getLabel() ?? $clientPrestataire->statut,
                        'valider_a' => $clientPrestataire->valider_a?->format('Y-m-d H:i:s'),
                        'prestataire' => $this->whenLoaded('prestataires.prestataire', function () use ($clientPrestataire) {
                            return [
                                'id' => $clientPrestataire->prestataire->id,
                                'raison_sociale' => $clientPrestataire->prestataire->raison_sociale,
                                'type_prestataire' => $clientPrestataire->prestataire->type_prestataire,
                                'type_prestataire_label' => $clientPrestataire->prestataire->type_prestataire?->getLabel() ?? 'Non défini',
                                'statut' => $clientPrestataire->prestataire->statut,
                                'statut_label' => $clientPrestataire->prestataire->statut?->getLabel() ?? 'Non défini',
                            ];
                        }),
                    ];
                });
            }),
        ];
    }
    
    /**
     * Formater les jours restants de manière lisible
     */
    private function formatJoursRestants($dateFin): string
    {
        $jours = now()->diffInDays($dateFin, false);
        
        if ($jours < 0) {
            return 'Expiré';
        } elseif ($jours === 0) {
            return 'Expire aujourd\'hui';
        } elseif ($jours === 1) {
            return 'Expire demain';
        } elseif ($jours < 7) {
            return "Expire dans {$jours} jours";
        } elseif ($jours < 30) {
            $semaines = floor($jours / 7);
            return "Expire dans {$semaines} semaine" . ($semaines > 1 ? 's' : '');
        } else {
            $mois = floor($jours / 30);
            return "Expire dans {$mois} mois";
        }
    }
}
