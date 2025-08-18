<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Facture extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'numero_facture',
        'sinistre_id',
        'prestataire_id',
        'montant_reclame',
        'montant_a_rembourser',
        'diagnostic',
        'ticket_moderateur',
        'statut',
        'motif_rejet',
        'est_valide_par_technicien',
        'technicien_id',
        'valide_par_technicien_a',
        'est_valide_par_medecin',
        'medecin_id',
        'valide_par_medecin_a',
        'est_autorise_par_comptable',
        'comptable_id',
        'autorise_par_comptable_a',
        'motif_rejet_technicien',
        'rejet_par_technicien_a',
        'motif_rejet_medecin',
        'rejet_par_medecin_a',
        'motif_rejet_comptable',
        'rejet_par_comptable_a',
    ];

    protected $casts = [
        'montant_reclame' => 'decimal:2',
        'montant_a_rembourser' => 'decimal:2',
        'ticket_moderateur' => 'decimal:2',
        'photo_justificatifs' => 'array',
        'est_valide_par_technicien' => 'boolean',
        'est_valide_par_medecin' => 'boolean',
        'est_autorise_par_comptable' => 'boolean',
        'valide_par_technicien_a' => 'datetime',
        'valide_par_medecin_a' => 'datetime',
        'autorise_par_comptable_a' => 'datetime',
        'rejet_par_technicien_a' => 'datetime',
        'rejet_par_medecin_a' => 'datetime',
        'rejet_par_comptable_a' => 'datetime',
        'statut' => \App\Enums\StatutFactureEnum::class,
    ];

    /**
     * Get the sinistre that owns the facture.
     */
    public function sinistre()
    {
        return $this->belongsTo(Sinistre::class);
    }

    /**
     * Get the prestataire that owns the facture.
     */
    public function prestataire()
    {
        return $this->belongsTo(Prestataire::class);
    }

    /**
     * Get the technicien that validated this facture.
     */
    public function technicien()
    {
        return $this->belongsTo(Personnel::class, 'technicien_id');
    }

    /**
     * Get the medecin that validated this facture.
     */
    public function medecin()
    {
        return $this->belongsTo(Personnel::class, 'medecin_id');
    }

    /**
     * Get the comptable that authorized this facture.
     */
    public function comptable()
    {
        return $this->belongsTo(Personnel::class, 'comptable_id');
    }

    /**
     * Get the lignes facture for this facture.
     */
    public function lignesFacture()
    {
        return $this->hasMany(LigneFacture::class);
    }

    /**
     * Check if facture is pending.
     */
    public function isPending()
    {
        return $this->statut === \App\Enums\StatutFactureEnum::EN_ATTENTE;
    }

    /**
     * Check if facture is validated by technicien.
     */
    public function isValidatedByTechnicien()
    {
        return $this->statut === \App\Enums\StatutFactureEnum::VALIDEE_TECHNICIEN && $this->est_valide_par_technicien;
    }

    /**
     * Check if facture is validated by medecin.
     */
    public function isValidatedByMedecin()
    {
        return $this->statut === \App\Enums\StatutFactureEnum::VALIDEE_MEDECIN && $this->est_valide_par_medecin;
    }

    /**
     * Check if facture is authorized by comptable.
     */
    public function isAuthorizedByComptable()
    {
        return $this->statut === \App\Enums\StatutFactureEnum::AUTORISEE_COMPTABLE && $this->est_autorise_par_comptable;
    }

    /**
     * Check if facture is reimbursed.
     */
    public function isReimbursed()
    {
        return $this->statut === \App\Enums\StatutFactureEnum::REMBOURSEE;
    }

    /**
     * Check if facture is rejected by technicien.
     */
    public function isRejectedByTechnicien()
    {
        return $this->statut === \App\Enums\StatutFactureEnum::REJETEE_TECHNICIEN;
    }

    /**
     * Check if facture is rejected by medecin.
     */
    public function isRejectedByMedecin()
    {
        return $this->statut === \App\Enums\StatutFactureEnum::REJETEE_MEDECIN;
    }

    /**
     * Check if facture is rejected by comptable.
     */
    public function isRejectedByComptable()
    {
        return $this->statut === \App\Enums\StatutFactureEnum::REJETEE_COMPTABLE;
    }

    /**
     * Check if facture is rejected (any type).
     */
    public function isRejected()
    {
        return in_array($this->statut, [
            \App\Enums\StatutFactureEnum::REJETEE,
            \App\Enums\StatutFactureEnum::REJETEE_TECHNICIEN,
            \App\Enums\StatutFactureEnum::REJETEE_MEDECIN,
            \App\Enums\StatutFactureEnum::REJETEE_COMPTABLE
        ]);
    }

    /**
     * Check if facture can be modified (rejected factures).
     */
    public function canBeModified()
    {
        return $this->isRejected();
    }

    /**
     * Reset facture to pending status after modification.
     */
    public function resetToPending()
    {
        $this->statut = \App\Enums\StatutFactureEnum::EN_ATTENTE;
        $this->est_valide_par_technicien = false;
        $this->est_valide_par_medecin = false;
        $this->est_autorise_par_comptable = false;
        $this->technicien_id = null;
        $this->medecin_id = null;
        $this->comptable_id = null;
        $this->valide_par_technicien_a = null;
        $this->valide_par_medecin_a = null;
        $this->autorise_par_comptable_a = null;
        $this->motif_rejet_technicien = null;
        $this->motif_rejet_medecin = null;
        $this->motif_rejet_comptable = null;
        $this->rejet_par_technicien_a = null;
        $this->rejet_par_medecin_a = null;
        $this->rejet_par_comptable_a = null;
        $this->save();
    }

    /**
     * Validate facture by technicien.
     */
    public function validateByTechnicien($technicienId)
    {
        $this->est_valide_par_technicien = true;
        $this->technicien_id = $technicienId;
        $this->valide_par_technicien_a = now();
        $this->statut = \App\Enums\StatutFactureEnum::VALIDEE_TECHNICIEN;
        $this->save();
    }

    /**
     * Validate facture by medecin.
     */
    public function validateByMedecin($medecinId)
    {
        $this->est_valide_par_medecin = true;
        $this->medecin_id = $medecinId;
        $this->valide_par_medecin_a = now();
        $this->statut = \App\Enums\StatutFactureEnum::VALIDEE_MEDECIN;
        $this->save();
    }

    /**
     * Authorize facture by comptable.
     */
    public function authorizeByComptable($comptableId)
    {
        $this->est_autorise_par_comptable = true;
        $this->comptable_id = $comptableId;
        $this->autorise_par_comptable_a = now();
        $this->statut = \App\Enums\StatutFactureEnum::AUTORISEE_COMPTABLE;
        $this->save();
    }

    /**
     * Reject facture by technicien.
     */
    public function rejectByTechnicien($technicienId, $motifRejet)
    {
        $this->statut = \App\Enums\StatutFactureEnum::REJETEE_TECHNICIEN;
        $this->motif_rejet_technicien = $motifRejet;
        $this->rejet_par_technicien_a = now();
        $this->technicien_id = $technicienId;
        $this->save();
    }

    /**
     * Reject facture by medecin.
     */
    public function rejectByMedecin($medecinId, $motifRejet)
    {
        $this->statut = \App\Enums\StatutFactureEnum::REJETEE_MEDECIN;
        $this->motif_rejet_medecin = $motifRejet;
        $this->rejet_par_medecin_a = now();
        $this->medecin_id = $medecinId;
        $this->save();
    }

    /**
     * Reject facture by comptable.
     */
    public function rejectByComptable($comptableId, $motifRejet)
    {
        $this->statut = \App\Enums\StatutFactureEnum::REJETEE_COMPTABLE;
        $this->motif_rejet_comptable = $motifRejet;
        $this->rejet_par_comptable_a = now();
        $this->comptable_id = $comptableId;
        $this->save();
    }

    /**
     * Reject facture (generic method for backward compatibility).
     */
    public function reject($motifRejet)
    {
        $this->statut = \App\Enums\StatutFactureEnum::REJETEE;
        $this->motif_rejet = $motifRejet;
        $this->save();
    }

    /**
     * Mark facture as reimbursed.
     */
    public function markAsReimbursed()
    {
        $this->statut = \App\Enums\StatutFactureEnum::REMBOURSEE;
        $this->save();
    }

    /**
     * Get the facture's status in French.
     */
    public function getStatutFrancaisAttribute()
    {
        return $this->statut->getLabel();
    }

    /**
     * Calculate the difference between claimed and reimbursed amounts.
     */
    public function getDifferenceAttribute()
    {
        return $this->montant_reclame - $this->montant_a_rembourser;
    }
}