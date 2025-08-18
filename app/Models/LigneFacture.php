<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LigneFacture extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'lignes_facture';

    protected $fillable = [
        'facture_id',
        'garantie_id',
        'libelle_acte',
        'prix_unitaire',
        'quantite',
        'prix_total',
        'taux_couverture',
        'montant_couvert',
        'ticket_moderateur',
    ];

    protected $casts = [
        'prix_unitaire' => 'decimal:2',
        'quantite' => 'integer',
        'prix_total' => 'decimal:2',
        'taux_couverture' => 'decimal:2',
        'montant_couvert' => 'decimal:2',
        'ticket_moderateur' => 'decimal:2',
    ];

    /**
     * Get the facture that owns this ligne facture.
     */
    public function facture()
    {
        return $this->belongsTo(Facture::class);
    }

    /**
     * Get the garantie for this ligne facture.
     */
    public function garantie()
    {
        return $this->belongsTo(Garantie::class);
    }

    /**
     * Calculate the coverage amount based on price and coverage rate.
     */
    public function calculateCoverage()
    {
        $this->montant_couvert = $this->prix_total * ($this->taux_couverture / 100);
        $this->ticket_moderateur = $this->prix_total - $this->montant_couvert;
    }

    /**
     * Boot method to auto-calculate totals.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($ligneFacture) {
            // Auto-calculate prix_total
            $ligneFacture->prix_total = $ligneFacture->prix_unitaire * $ligneFacture->quantite;
            
            // Auto-calculate coverage amounts
            $ligneFacture->calculateCoverage();
        });
    }
}