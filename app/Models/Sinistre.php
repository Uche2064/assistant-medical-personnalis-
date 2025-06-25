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
        'assure_id',
    ];

    protected function casts(): array
    {
        return [
            'date_sinistre' => 'date',
        ];
    }


    public function assure()
    {
        return $this->belongsTo(Assure::class);
    }

    public function prestataire()
    {
        return $this->belongsTo(Prestataire::class);
    }

   
    public function factures()
    {
        return $this->hasMany(Facture::class);
    }


    public function getTotalMontantReclame(): float
    {
        return $this->factures()->sum('montant_reclame');
    }


    public function getTotalMontantARembourser(): float
    {
        return $this->factures()->sum('montant_a_rembourser');
    }

    
}
