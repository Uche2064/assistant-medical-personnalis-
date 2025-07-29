<?php

namespace App\Models;

use App\Enums\StatutPropositionContratEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropositionContrat extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'demande_adhesion_id',
        'contrat_id',
        'prime_proposee',
        'taux_couverture',
        'frais_gestion',
        'commentaires_technicien',
        'technicien_id',
        'statut',
        'date_proposition',
        'date_acceptation',
        'date_refus',
    ];

    protected $casts = [
        'statut' => StatutPropositionContratEnum::class,
        'date_proposition' => 'datetime',
        'date_acceptation' => 'datetime',
        'date_refus' => 'datetime',
        'taux_couverture' => 'decimal:2',
        'frais_gestion' => 'decimal:2',
        'prime_proposee' => 'decimal:2',
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
        return $this->belongsTo(Contrat::class);
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
    public function garanties()
    {
        return $this->belongsToMany(Garantie::class, 'proposition_contrat_garantie');
    }

    /**
     * Check if proposition is pending.
     */
    public function isProposee()
    {
        return $this->statut === StatutPropositionContratEnum::PROPOSEE;
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