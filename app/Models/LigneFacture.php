<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LigneFacture extends Model
{
    use HasFactory;
    protected $table = 'lignes_facture';

    protected $fillable = [
        'facture_id',
        'libelle',
        'prix_unitaire',
        'quantite',
        // Derived fields can be calculated if needed
    ];

    protected $casts = [
        'prix_unitaire' => 'decimal:2',
        'quantite' => 'integer',
        // No derived casts in schema
    ];

    /**
     * Get the facture that owns this ligne facture.
     */
    public function facture()
    {
        return $this->belongsTo(Facture::class);
    }

    // No garantie relation in new schema

    /**
     * Calculate the coverage amount based on price and coverage rate.
     */
    // Coverage calculations handled at facture/type contrat level

    /**
     * Boot method to auto-calculate totals.
     */
    // No boot override needed for new schema
}
