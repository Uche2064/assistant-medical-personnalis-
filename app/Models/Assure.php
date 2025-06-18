<?php

namespace App\Models;

use App\Enums\LienParenteEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assure extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'utilisateur_id',
        'client_id',
        'lien_parente',
        'assure_parent_id'
    ];

    protected function casts(): array
    {
        return [
            'lien_parente' => LienParenteEnum::class,
        ];
    }

    /**
     * Get the user that owns the assure.
     */
    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'utilisateur_id');
    }

    /**
     * Get the client that owns the assure.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the parent assure (for dependents).
     */
    public function assureParent()
    {
        return $this->belongsTo(Assure::class, 'assure_parent_id');
    }

    /**
     * Get the children assures (dependents).
     */
    public function assureEnfants()
    {
        return $this->hasMany(Assure::class, 'assure_parent_id');
    }

    /**
     * Get the sinistres for the assure.
     */
    public function sinistres()
    {
        return $this->hasMany(Sinistre::class);
    }

    /**
     * Get the prestataires linked to this assure.
     */
    public function prestataires()
    {
        return $this->belongsToMany(Prestataire::class, 'prestataire_assure');
    }

    /**
     * Get the garanties for this assure.
     */
    public function garanties()
    {
        return $this->belongsToMany(Garantie::class, 'assure_garantie')
                    ->withPivot('date_debut', 'date_fin', 'est_actif')
                    ->withTimestamps();
    }

    /**
     * Check if assure is the principal (main) insured.
     */
    public function isPrincipal(): bool
    {
        return $this->lien_parente === LienParenteEnum::PRINCIPAL;
    }

    /**
     * Check if assure is a dependent.
     */
    public function isDependent(): bool
    {
        return !$this->isPrincipal() && $this->assure_parent_id !== null;
    }

    /**
     * Get active garanties for this assure.
     */
    public function getActiveGaranties()
    {
        return $this->garanties()
                    ->wherePivot('est_actif', true)
                    ->wherePivot('date_debut', '<=', now())
                    ->where(function($query) {
                        $query->wherePivot('date_fin', '>=', now())
                              ->orWherePivot('date_fin', null);
                    });
    }

    /**
     * Check if assure has a specific garantie.
     */
    public function hasGarantie(Garantie $garantie): bool
    {
        return $this->getActiveGaranties()
                    ->where('garanties.id', $garantie->id)
                    ->exists();
    }

    /**
     * Get total amount claimed by this assure.
     */
    public function getTotalMontantReclame(): float
    {
        return $this->sinistres()
                    ->with('factures')
                    ->get()
                    ->sum(function($sinistre) {
                        return $sinistre->factures->sum('montant_reclame');
                    });
    }

    /**
     * Get total amount reimbursed to this assure.
     */
    public function getTotalMontantRembourse(): float
    {
        return $this->sinistres()
                    ->with('factures')
                    ->get()
                    ->sum(function($sinistre) {
                        return $sinistre->factures
                                       ->where('valide_comptable', true)
                                       ->sum('montant_a_rembourser');
                    });
    }

    /**
     * Scope to get principal assures only.
     */
    public function scopePrincipal($query)
    {
        return $query->where('lien_parente', LienParenteEnum::PRINCIPAL);
    }

    /**
     * Scope to get dependent assures only.
     */
    public function scopeDependents($query)
    {
        return $query->where('lien_parente', '!=', LienParenteEnum::PRINCIPAL)
                    ->whereNotNull('assure_parent_id');
    }

    /**
     * Scope to get assures by family relationship.
     */
    public function scopeByLienParente($query, LienParenteEnum $lienParente)
    {
        return $query->where('lien_parente', $lienParente);
    }
}
