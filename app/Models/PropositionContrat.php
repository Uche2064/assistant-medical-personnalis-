<?php

namespace App\Models;

use App\Enums\StatutPropositionContratEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropositionContrat extends Model
{
    use HasFactory;

    protected $table = 'propositions_contrats';

    protected $fillable = [
        'demande_adhesion_id',
        'contrat_id',
        'commentaires_technicien',
        'technicien_id',
        'statut',
        'date_acceptation',
        'date_refus',
    ];

    protected $casts = [
        'statut' => StatutPropositionContratEnum::class,
        'date_proposition' => 'datetime',
        'date_acceptation' => 'datetime',
        'date_refus' => 'datetime',
    ];

    /**
     * Get the demande adhesion that owns the proposition.
     */
    public function demandeAdhesion()
    {
        return $this->belongsTo(DemandeAdhesion::class);
    }

    /**
     * Get the contrat for this proposition.
     */
    public function contrat()
    {
        // Now references types_contrats
        return $this->belongsTo(TypeContrat::class, 'contrat_id');
    }

    /**
     * Get the technicien who made this proposition.
     */
    public function technicien()
    {
        return $this->belongsTo(Personnel::class, 'technicien_id');
    }

    /**
     * Get the garanties for this proposition.
     */
    // No pivot defined in migrations; remove garanties relation to avoid runtime errors

    /**
     * Check if proposition is pending.
     */
    public function isProposee()
    {
        return $this->statut === StatutPropositionContratEnum::PROPOSEE;
    }

    /**
     * Get the prime from the associated contract.
     */
    public function getPrimeAttribute()
    {
        return $this->contrat->prime_standard ?? 0;
    }

    /**
     * Get the formatted prime from the associated contract.
     */
    public function getPrimeFormattedAttribute()
    {
        return number_format($this->getPrimeAttribute(), 0, ',', ' ') . ' FCFA';
    }

    /**
     * Get the taux_couverture from the associated contract (default 80%).
     */
    public function getTauxCouvertureAttribute()
    {
        return 80; // Valeur par défaut
    }

    /**
     * Get the frais_gestion from the associated contract (default 20%).
     */
    public function getFraisGestionAttribute()
    {
        return 20; // Valeur par défaut
    }

    /**
     * Get the total prime with fees.
     */
    public function getPrimeTotaleAttribute()
    {
        $prime = $this->getPrimeAttribute();
        $fraisGestion = $this->getFraisGestionAttribute();
        return $prime + ($prime * $fraisGestion / 100);
    }

    /**
     * Get the formatted total prime.
     */
    public function getPrimeTotaleFormattedAttribute()
    {
        return number_format($this->getPrimeTotaleAttribute(), 0, ',', ' ') . ' FCFA';
    }

    /**
     * Check if proposition is accepted.
     */
    public function isAcceptee()
    {
        return $this->statut === StatutPropositionContratEnum::ACCEPTEE;
    }

    /**
     * Check if proposition is refused.
     */
    public function isRefusee()
    {
        return $this->statut === StatutPropositionContratEnum::REFUSEE;
    }

    /**
     * Check if proposition is expired.
     */
    public function isExpiree()
    {
        return $this->statut === StatutPropositionContratEnum::EXPIREE;
    }

    /**
     * Accept the proposition.
     */
    public function accepter()
    {
        $this->statut = StatutPropositionContratEnum::ACCEPTEE;
        $this->date_acceptation = now();
        $this->save();
    }

    /**
     * Refuse the proposition.
     */
    public function refuser()
    {
        $this->statut = StatutPropositionContratEnum::REFUSEE;
        $this->date_refus = now();
        $this->save();
    }

    /**
     * Expire the proposition.
     */
    public function expirer()
    {
        $this->statut = StatutPropositionContratEnum::EXPIREE;
        $this->save();
    }
} 
