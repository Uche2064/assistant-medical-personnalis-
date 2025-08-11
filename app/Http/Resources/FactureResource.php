<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FactureResource extends JsonResource
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
            'numero_facture' => $this->numero_facture,
            'sinistre_id' => $this->sinistre_id,
            'prestataire_id' => $this->prestataire_id,
            'montant_reclame' => $this->montant_reclame,
            'montant_a_rembourser' => $this->montant_a_rembourser,
            'diagnostic' => $this->diagnostic,
            'photo_justificatifs' => $this->photo_justificatifs,
            'ticket_moderateur' => $this->ticket_moderateur,
            'statut' => $this->statut,
            'statut_libelle' => $this->statut->getLabel(),
            'motif_rejet' => $this->motif_rejet,
            'est_valide_par_technicien' => $this->est_valide_par_technicien,
            'technicien_id' => $this->technicien_id,
            'valide_par_technicien_a' => $this->valide_par_technicien_a,
            'est_valide_par_medecin' => $this->est_valide_par_medecin,
            'medecin_id' => $this->medecin_id,
            'valide_par_medecin_a' => $this->valide_par_medecin_a,
            'est_autorise_par_comptable' => $this->est_autorise_par_comptable,
            'comptable_id' => $this->comptable_id,
            'autorise_par_comptable_a' => $this->autorise_par_comptable_a,
            'lignes_facture' => LigneFactureResource::collection($this->whenLoaded('lignesFacture')),
            'sinistre' => $this->when($this->relationLoaded('sinistre'), function () {
                return [
                    'id' => $this->sinistre->id,
                    'description' => $this->sinistre->description,
                    'date_sinistre' => $this->sinistre->date_sinistre,
                    'assure' => [
                        'id' => $this->sinistre->assure->id,
                        'nom' => $this->sinistre->assure->nom,
                        'prenoms' => $this->sinistre->assure->prenoms,
                        'email' => $this->sinistre->assure->email,
                        'est_principal' => $this->sinistre->assure->est_principal,
                    ],
                ];
            }),
            'prestataire' => $this->when($this->relationLoaded('prestataire'), function () {
                return [
                    'id' => $this->prestataire->id,
                    'raison_sociale' => $this->prestataire->raison_sociale,
                    'type_prestataire' => $this->prestataire->type_prestataire,
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
