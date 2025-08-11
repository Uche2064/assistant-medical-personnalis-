<?php

namespace App\Models;

use App\Enums\StatutPrestataireEnum;
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
        'raison_sociale',
        'documents_requis',
        'code_parrainage',
        'medecin_controleur_id',
    ];

    protected $casts = [
        'type_prestataire' => TypePrestataireEnum::class,
        'documents_requis' => 'array',
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
     * Check if prestataire is pending.
     */
    public function isPending()
    {
        return $this->statut === \App\Enums\StatutPrestataireEnum::EN_ATTENTE;
    }

    /**
     * Check if prestataire is validated.
     */
    public function isValidated()
    {
        return $this->statut === \App\Enums\StatutPrestataireEnum::VALIDE;
    }

    /**
     * Check if prestataire is rejected.
     */
    public function isRejected()
    {
        return $this->statut === \App\Enums\StatutPrestataireEnum::REJETE;
    }

    /**
     * Check if prestataire is suspended.
     */
    public function isSuspended()
    {
        return $this->statut === \App\Enums\StatutPrestataireEnum::SUSPENDU;
    }

    /**
     * Validate the prestataire.
     */
    public function validate()
    {
        $this->statut = \App\Enums\StatutPrestataireEnum::VALIDE;
        $this->save();
    }

    /**
     * Reject the prestataire.
     */
    public function reject()
    {
        $this->statut = \App\Enums\StatutPrestataireEnum::REJETE;
        $this->save();
    }

    /**
     * Suspend the prestataire.
     */
    public function suspend()
    {
        $this->statut = \App\Enums\StatutPrestataireEnum::SUSPENDU;
        $this->save();
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
