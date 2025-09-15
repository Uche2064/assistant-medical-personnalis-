<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Facture extends Model
{
    use HasFactory;

    protected $fillable = [
        'diagnostic',
        'numero_facture',
        'sinistre_id',
        'prestataire_id',
        'montant_facture',
        'ticket_moderateur',
        'statut',
        'motif_rejet',
        'technicien_id',
        'valide_par_technicien_a',
        'medecin_id',
        'valide_par_medecin_a',
        'comptable_id',
        'autorise_par_comptable_a',
    ];

    protected $casts = [
        'montant_facture' => 'decimal:2',
        'ticket_moderateur' => 'decimal:2',
        'valide_par_technicien_a' => 'datetime',
        'valide_par_medecin_a' => 'datetime',
        'autorise_par_comptable_a' => 'datetime',
        'statut' => 'string',
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
        // New FK points to users table
        return $this->belongsTo(User::class, 'prestataire_id');
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
        return $this->statut === 'en_attente';
    }

    /**
     * Check if facture is validated by technicien.
     */
    public function isValidatedByTechnicien()
    {
        return $this->statut === 'validee_technicien' && $this->valide_par_technicien_a !== null;
    }

    /**
     * Check if facture is validated by medecin.
     */
    public function isValidatedByMedecin()
    {
        return $this->statut === 'validee_medecin' && $this->valide_par_medecin_a !== null;
    }

    /**
     * Check if facture is authorized by comptable.
     */
    public function isAuthorizedByComptable()
    {
        return $this->statut === 'autorisee_comptable' && $this->autorise_par_comptable_a !== null;
    }

    /**
     * Check if facture is reimbursed.
     */
    public function isReimbursed()
    {
        return $this->statut === 'rembourse';
    }

    /**
     * Check if facture is rejected by technicien.
     */
    public function isRejectedByTechnicien()
    {
        return $this->statut === 'rejetee_technicien';
    }

    /**
     * Check if facture is rejected by medecin.
     */
    public function isRejectedByMedecin()
    {
        return $this->statut === 'rejetee_medecin';
    }

    /**
     * Check if facture is rejected by comptable.
     */
    public function isRejectedByComptable()
    {
        return $this->statut === 'rejetee_comptable';
    }

    /**
     * Check if facture is rejected (any type).
     */
    public function isRejected()
    {
        return in_array($this->statut, [
            'rejetee',
            'rejetee_technicien',
            'rejetee_medecin',
            'rejetee_comptable',
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
        $this->statut = 'en_attente';
        $this->technicien_id = null;
        $this->medecin_id = null;
        $this->comptable_id = null;
        $this->valide_par_technicien_a = null;
        $this->valide_par_medecin_a = null;
        $this->autorise_par_comptable_a = null;
        $this->save();
    }

    /**
     * Validate facture by technicien.
     */
    public function validateByTechnicien($technicienId)
    {
        $this->technicien_id = $technicienId;
        $this->valide_par_technicien_a = now();
        $this->statut = 'validee_technicien';
        $this->save();
    }

    /**
     * Validate facture by medecin.
     */
    public function validateByMedecin($medecinId)
    {
        $this->medecin_id = $medecinId;
        $this->valide_par_medecin_a = now();
        $this->statut = 'validee_medecin';
        $this->save();
    }

    /**
     * Authorize facture by comptable.
     */
    public function authorizeByComptable($comptableId)
    {
        $this->comptable_id = $comptableId;
        $this->autorise_par_comptable_a = now();
        $this->statut = 'autorisee_comptable';
        $this->save();
    }

    /**
     * Reject facture by technicien.
     */
    public function rejectByTechnicien($technicienId, $motifRejet)
    {
        $this->statut = 'rejetee_technicien';
        $this->technicien_id = $technicienId;
        $this->save();
    }

    /**
     * Reject facture by medecin.
     */
    public function rejectByMedecin($medecinId, $motifRejet)
    {
        $this->statut = 'rejetee_medecin';
        $this->medecin_id = $medecinId;
        $this->save();
    }

    /**
     * Reject facture by comptable.
     */
    public function rejectByComptable($comptableId, $motifRejet)
    {
        $this->statut = 'rejetee_comptable';
        $this->comptable_id = $comptableId;
        $this->save();
    }

    /**
     * Reject facture (generic method for backward compatibility).
     */
    public function reject($motifRejet)
    {
        $this->statut = 'rejetee';
        $this->motif_rejet = $motifRejet;
        $this->save();
    }

    /**
     * Mark facture as reimbursed.
     */
    public function markAsReimbursed()
    {
        $this->statut = 'rembourse';
        $this->save();
    }

    /**
     * Get the facture's status in French.
     */
    public function getStatutFrancaisAttribute()
    {
        // Simple mapping since statut is now string
        return match ($this->statut) {
            'en_attente' => 'En attente',
            'validee_technicien' => 'Validée Technicien',
            'validee_medecin' => 'Validée Médecin',
            'autorisee_comptable' => 'Autorisée Comptable',
            'rejetee' => 'Rejetée',
            'rejetee_technicien' => 'Rejetée Technicien',
            'rejetee_medecin' => 'Rejetée Médecin',
            'rejetee_comptable' => 'Rejetée Comptable',
            'rembourse' => 'Remboursée',
            default => ucfirst(str_replace('_', ' ', $this->statut)),
        };
    }

    /**
     * Calculate the difference between claimed and reimbursed amounts.
     */
    public function getDifferenceAttribute()
    {
        return ($this->montant_facture ?? 0) - ($this->ticket_moderateur ?? 0);
    }
}
