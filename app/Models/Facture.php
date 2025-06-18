<?php

namespace App\Models;

use App\Enums\StatutFactureEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Facture extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'numero_facture',
        'montant_reclame',
        'montant_a_rembourser',
        'diagnostic',
        'photo_justificatifs',
        'ticket_moderateur',
        'statut',
        'sinistre_id',
        'prestataire_id',
        'medecin_id',
        'technicien_id',
        'comptable_id',
        'est_valide_par_medecin',
        'est_valide_par_technicien',
        'est_autorise_par_comptable',
        'valide_par_medecin_a',
        'valide_par_technicien_a',
        'autorise_par_comptable_a'
    ];

    protected function casts(): array
    {
        return [
            'montant_reclame' => 'decimal:2',
            'montant_a_rembourser' => 'decimal:2',
            'ticket_moderateur' => 'decimal:2',
            'photo_justificatifs' => 'array',
            'statut' => StatutFactureEnum::class,
            'est_valide_par_medecin' => 'boolean',
            'est_valide_par_technicien' => 'boolean',
            'est_autorise_par_comptable' => 'boolean',
            'valide_par_medecin_a' => 'datetime',
            'valide_par_technicien_a' => 'datetime',
            'autorise_par_comptable_a' => 'datetime',
        ];
    }

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
     * Get the medecin controleur who validated the facture.
     */
    public function medecin()
    {
        return $this->belongsTo(Personnel::class, 'medecin_id');
    }

    /**
     * Get the technicien who validated the facture.
     */
    public function technicien()
    {
        return $this->belongsTo(Personnel::class, 'technicien_id');
    }

    /**
     * Get the comptable who authorized the facture.
     */
    public function comptable()
    {
        return $this->belongsTo(Personnel::class, 'comptable_id');
    }

    /**
     * Check if facture is validated by medecin.
     */
    public function isValidatedByMedecin(): bool
    {
        return $this->valide_medecin;
    }

    /**
     * Check if facture is validated by technicien.
     */
    public function isValidatedByTechnicien(): bool
    {
        return $this->valide_technicien;
    }

    /**
     * Check if facture is authorized by comptable.
     */
    public function isAuthorizedByComptable(): bool
    {
        return $this->valide_comptable;
    }

    /**
     * Check if facture is fully validated.
     */
    public function isFullyValidated(): bool
    {
        return $this->valide_medecin && $this->valide_technicien && $this->valide_comptable;
    }

    /**
     * Validate by medecin controleur.
     */
    public function validateByMedecin(Personnel $medecin): void
    {
        $this->update([
            'valide_medecin' => true,
            'medecin_id' => $medecin->id,
            'valide_medecin_a' => now(),
            'statut' => StatutFactureEnum::VALIDE_MEDECIN
        ]);
    }

    /**
     * Validate by technicien.
     */
    public function validateByTechnicien(Personnel $technicien): void
    {
        $this->update([
            'valide_technicien' => true,
            'technicien_id' => $technicien->id,
            'valide_technicien_a' => now(),
            'statut' => StatutFactureEnum::VALIDE_TECHNICIEN
        ]);
    }

    /**
     * Authorize by comptable.
     */
    public function authorizeByComptable(Personnel $comptable): void
    {
        $this->update([
            'valide_comptable' => true,
            'comptable_id' => $comptable->id,
            'valide_comptable_a' => now(),
            'statut' => StatutFactureEnum::AUTORISER_PAIEMENT
        ]);
    }

    /**
     * Calculate the ticket moderateur.
     */
    public function calculateTicketModerateur(): float
    {
        // This would contain business logic for calculating ticket moderateur
        // Based on insurance coverage, type of care, etc.
        return $this->montant_reclame - $this->montant_a_rembourser;
    }

    /**
     * Scope to get factures pending medecin validation.
     */
    public function scopePendingMedecinValidation($query)
    {
        return $query->where('valide_medecin', false);
    }

    /**
     * Scope to get factures pending technicien validation.
     */
    public function scopePendingTechnicienValidation($query)
    {
        return $query->where('valide_medecin', true)
                    ->where('valide_technicien', false);
    }

    /**
     * Scope to get factures pending comptable authorization.
     */
    public function scopePendingComptableAuthorization($query)
    {
        return $query->where('valide_medecin', true)
                    ->where('valide_technicien', true)
                    ->where('valide_comptable', false);
    }

    /**
     * Scope to get fully validated factures.
     */
    public function scopeFullyValidated($query)
    {
        return $query->where('valide_medecin', true)
                    ->where('valide_technicien', true)
                    ->where('valide_comptable', true);
    }
}