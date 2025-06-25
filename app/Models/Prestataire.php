<?php

namespace App\Models;

use App\Enums\TypePrestataireEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prestataire extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'type_prestataire',
        'medecin_controleur_id'
    ];

    protected function casts(): array
    {
        return [
            'type_prestataire' => TypePrestataireEnum::class,
        ];
    }

  
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    public function medecinControleur()
    {
        return $this->belongsTo(Personnel::class, 'medecin_controleur_id');
    }
    public function demandesAdhesion()
    {
        return $this->hasMany(DemandeAdhesion::class);
    }

    public function factures()
    {
        return $this->hasMany(Facture::class);
    }
    public function assures()
    {
        return $this->belongsToMany(Assure::class, 'prestataire_assure');
    }
}
