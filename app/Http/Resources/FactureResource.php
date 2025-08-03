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
            'numero_facture' => $this->numero_facture,
            'sinistre_id' => $this->sinistre_id,
            'prestataire_id' => $this->prestataire_id,
            'montant_reclame' => $this->montant_reclame,
            'montant_a_rembourser' => $this->montant_a_rembourser,
            'diagnostic' => $this->diagnostic,
            'photo_justificatifs' => $this->photo_justificatifs,
            'ticket_moderateur' => $this->ticket_moderateur,
            'statut' => $this->statut,
            'motif_rejet' => $this->motif_rejet,
            'est_valide_par_technicien' => $this->est_valide_par_technicien,
            'technicien_id' => $this->technicien_id,
            'valide_par_technicien_a' => $this->valide_par_technicien_a,
            'est_valide_par_medecin' => $this->est_valide_par_medecin,
            'medecin_id' => $this->medecin_id,
            'valide_par_medecin_a' => $this->valide_par_medecin_a,
            'est_autorise_par_comptable' => $this->est_autorise_par_comptable,
            'comptable_id' => $this->comptable_id,
            'autorise_par_comptable_a' => $this->autorise_par_comptable_a
        ];
    }
}
