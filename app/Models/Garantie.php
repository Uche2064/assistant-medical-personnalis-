<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Garantie extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'libelle_garantie',
        'contrat_id',
        'categorie_garantie_id',
        'plafond',
        'taux_couverture',
    ];

    protected function casts(): array
    {
        return [
            'plafond' => 'decimal:2',
            'taux_couverture' => 'decimal:2',
        ];
    }

    /**
     * Get the categorie garantie that owns the garantie.
     */
    public function categorieGarantie()
    {
        return $this->belongsTo(CategorieGarantie::class);
    }

    /**
     * Get the compagnie that owns the garantie.
     */
    public function compagnie()
    {
        return $this->belongsTo(Compagnie::class);
    }

    /**
     * Get the assures for this garantie.
     */
    public function assures()
    {
        return $this->belongsToMany(Assure::class, 'assure_garantie')
                    ->withPivot('date_debut', 'date_fin', 'est_actif')
                    ->withTimestamps();
    }

    /**
     * Calculate coverage amount for a given claim amount.
     */
    public function calculateCoverage(float $montantReclame): float
    {
        // Apply percentage coverage
        $montantCouvert = $montantReclame * ($this->taux_couverture / 100);
        
        // Apply maximum limit
        return min($montantCouvert, $this->plafond);
    }

    /**
     * Check if amount is within coverage limits.
     */
    public function isWithinLimits(float $montant): bool
    {
        return $montant <= $this->plafond;
    }

    /**
     * Scope to get garanties by category.
     */
    public function scopeByCategory($query, $categorieId)
    {
        return $query->where('categorie_garantie_id', $categorieId);
    }
}