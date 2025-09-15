<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sinistre extends Model
{
    use HasFactory;

    protected $fillable = [
        'assure_id',
        'prestataire_id',
        'description',
        'statut',
    ];

    protected $casts = [
        'statut' => 'string',
    ];
    

    /**
     * Get the assure that owns the sinistre.
     */
    public function assure()
    {
        return $this->belongsTo(Assure::class);
    }

    /**
     * Get the prestataire that owns the sinistre.
     */
    public function prestataire()
    {
        return $this->belongsTo(Prestataire::class);
    }

    /**
     * Get the factures for this sinistre.
     */
    public function factures()
    {
        return $this->hasMany(Facture::class);
    }

  

    /**
     * Check if sinistre is in progress.
     */
    public function isInProgress()
    {
        return $this->statut === 'en_cours';
    }

    /**
     * Check if sinistre is closed.
     */
    public function isClosed()
    {
        return $this->statut === 'cloture';
    }

    /**
     * Update sinistre status.
     */
    public function updateStatus($status)
    {
        $this->statut = $status;
        $this->save();
    }

    /**
     * Get the total amount claimed for this sinistre.
     */
    public function getTotalAmountClaimedAttribute()
    {
        return $this->factures()->sum('montant_reclame');
    }

    /**
     * Get the total amount to reimburse for this sinistre.
     */
    public function getTotalAmountToReimburseAttribute()
    {
        return $this->factures()->sum('montant_a_rembourser');
    }
}
