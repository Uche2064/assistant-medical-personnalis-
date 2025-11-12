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
            'client_id' => $this->client_id,
            'type_contrat_id' => $this->type_contrat_id,
            'date_debut' => $this->date_debut?->format('Y-m-d'),
            'date_debut_formatted' => $this->date_debut?->format('d/m/Y'),
            'date_fin' => $this->date_fin?->format('Y-m-d'),
            'date_fin_formatted' => $this->date_fin?->format('d/m/Y'),
            'statut' => $this->statut?->value ?? $this->statut,
            'statut_label' => $this->statut?->getLabel() ?? $this->statut,
            'numero_police' => $this->numero_police,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),

            // Données calculées
            'jours_restants' => $this->date_fin ? max(0, now()->diffInDays($this->date_fin, false)) : 0,
            'jours_restants_formatted' => $this->date_fin ? $this->formatJoursRestants($this->date_fin) : 'N/A',

            // Relation avec le contrat
            'contrat' => $this->whenLoaded('typeContrat', function () {
                return [
                    'id' => $this->typeContrat->id,
                    'libelle' => $this->typeContrat->libelle,
                    'prime_standard' => $this->typeContrat->prime_standard,
                    'prime_standard_formatted' => number_format($this->typeContrat->prime_standard, 0, ',', ' ') . ' FCFA',
                    'est_actif' => $this->typeContrat->est_actif,
                    'categories_garanties' => $this->whenLoaded('typeContrat.categoriesGaranties', function () {
                        return $this->typeContrat->categoriesGaranties->map(function ($categorie) {
                            return [
                                'id' => $categorie->id,
                                'libelle' => $categorie->libelle,
                                'libelle_formatted' => ucfirst($categorie->libelle),
                                'description' => $categorie->description,
                                'couverture' => $categorie->pivot->couverture ?? null,
                                'couverture_formatted' => ($categorie->pivot->couverture ?? 0) . '%',
                                'garanties' => $this->whenLoaded('typeContrat.categoriesGaranties.garanties', function () use ($categorie) {
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
                    'type_client' => $this->client->type_client?->value ?? $this->client->type_client,
                    'type_client_label' => $this->client->type_client?->getLabel() ?? 'Non défini',
                    'user' => $this->whenLoaded('client.user', function () {
                        return [
                            'id' => $this->client->user->id,
                            'email' => $this->client->user->email,
                            'contact' => $this->client->user->contact,
                            'adresse' => $this->client->user->adresse,
                            'personne' => $this->whenLoaded('client.user.personne', function () {
                                return [
                                    'id' => $this->client->user->personne->id,
                                    'nom' => $this->client->user->personne->nom,
                                    'prenoms' => $this->client->user->personne->prenoms,
                                    'nom_complet' => $this->client->user->personne->nom . ' ' . $this->client->user->personne->prenoms,
                                    'date_naissance' => $this->client->user->personne->date_naissance?->format('Y-m-d'),
                                    'date_naissance_formatted' => $this->client->user->personne->date_naissance?->format('d/m/Y'),
                                    'sexe' => $this->client->user->personne->sexe,
                                    'sexe_label' => $this->client->user->personne->sexe?->getLabel() ?? 'Non défini',
                                    'profession' => $this->client->user->personne->profession,
                                ];
                            }),
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
