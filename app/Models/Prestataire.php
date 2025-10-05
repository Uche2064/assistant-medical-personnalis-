<?php

namespace App\Models;

use App\Enums\StatutPrestataireEnum;
use App\Enums\TypePrestataireEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prestataire extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type_prestataire',
        'statut',
    ];

    protected $casts = [
        'type_prestataire' => TypePrestataireEnum::class,
        'statut' => StatutPrestataireEnum::class
    ];

    /**
     * Get the user that owns the prestataire.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the medecin controleur that manages this prestataire.
     */
    public function medecinControleur()
    {
        return $this->belongsTo(Personnel::class, 'medecin_controleur_id');
    }

    /**
     * Get the sinistres for this prestataire.
     */
    public function sinistres()
    {
        return $this->hasMany(Sinistre::class);
    }

    /**
     * Get the factures for this prestataire.
     */
    public function factures()
    {
        return $this->hasMany(Facture::class);
    }

    /**
     * Get the demandes adhesions for this prestataire.
     */
    public function demandesAdhesions()
    {
        return $this->user->demandesAdhesions();
    }


    /**
     * Get the prestataire's name.
     */
    public function getNameAttribute()
    {
        return $this->nom_etablissement;
    }

    /**
     * Get the prestataire's type in French.
     */
    public function getTypeFrancaisAttribute()
    {
        return $this->type_prestataire->getLabel();
    }
}
