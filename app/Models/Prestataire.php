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

    /**
     * Get the user that owns the prestataire.
     */
    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the medecin controleur for the prestataire.
     */
    public function medecinControleur()
    {
        return $this->belongsTo(Personnel::class, 'medecin_controleur_id');
    }

    /**
     * Get the demandes d'adhesion for the prestataire.
     */
    public function demandesAdhesion()
    {
        return $this->hasMany(DemandeAdhesion::class);
    }

    /**
     * Get the factures for the prestataire.
     */
    public function factures()
    {
        return $this->hasMany(Facture::class);
    }

    /**
     * Get the assures linked to this prestataire.
     */
    public function assures()
    {
        return $this->belongsToMany(Assure::class, 'prestataire_assure');
    }
}
