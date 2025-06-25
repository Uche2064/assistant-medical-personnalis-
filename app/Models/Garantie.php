<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Garantie extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'libelle',
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

    public function categorieGarantie()
    {
        return $this->belongsTo(CategorieGarantie::class);
    }

    public function assures()
    {
        return $this->belongsToMany(Assure::class, 'assure_garantie')
                    ->withPivot('date_debut', 'date_fin', 'est_actif')
                    ->withTimestamps();
    }

    public function calculateCoverage(float $montantReclame): float
    {
        $montantCouvert = $montantReclame * ($this->taux_couverture / 100);
        
        return min($montantCouvert, $this->plafond);
    }
    public function isWithinLimits(float $montant): bool
    {
        return $montant <= $this->plafond;
    }

    public function scopeByCategory($query, $categorieId)
    {
        return $query->where('categorie_garantie_id', $categorieId);
    }
}