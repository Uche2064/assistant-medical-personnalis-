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
        'categorie_garantie_id',
        'medecin_controleur_id',
        'plafond',
        'prix_standard',
        'taux_couverture',
    ];

    protected $casts = [
        'plafond' => 'decimal:2',
        'prix_standard' => 'decimal:2',
        'taux_couverture' => 'decimal:2',
    ];

    /**
     * Get the categorie garantie that owns this garantie.
     */
    public function categorieGarantie()
    {
        return $this->belongsTo(CategorieGarantie::class);
    }

    /**
     * Get the medecin controleur that manages this garantie.
     */
    public function medecinControleur()
    {
        return $this->belongsTo(Personnel::class, 'medecin_controleur_id');
    }

    /**
     * Calculate the coverage amount based on the standard price.
     */
    public function getCoverageAmountAttribute()
    {
        return $this->prix_standard * ($this->taux_couverture / 100);
    }

    /**
     * Check if garantie is within the limit.
     */
    public function isWithinLimit($amount)
    {
        return $amount <= $this->plafond;
    }
}