<?php

namespace App\Models;

use App\Enums\StatutSinistreEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sinistre extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'description',
        'date_sinistre',
        'client_id',
        'prestataire_id'
    ];

    protected function casts(): array
    {
        return [
            'date_sinistre' => 'date',
        ];
    }

    /**
     * Get the assure that owns the sinistre.
     */
    public function assure()
    {
        return $this->belongsTo(Assure::class);
    }

    /**
     * Get the prestataire associated with the sinistre.
     */
    public function prestataire()
    {
        return $this->belongsTo(Prestataire::class);
    }

    /**
     * Get the factures for the sinistre.
     */
    public function factures()
    {
        return $this->hasMany(Facture::class);
    }

    /**
     * Get total amount claimed for this sinistre.
     */
    public function getTotalMontantReclame(): float
    {
        return $this->factures()->sum('montant_reclame');
    }

    /**
     * Get total amount to be reimbursed for this sinistre.
     */
    public function getTotalMontantARembourser(): float
    {
        return $this->factures()->sum('montant_a_rembourser');
    }

    
}
