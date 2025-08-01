<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assure extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'client_id',
        'entreprise_id',
        'assure_principal_id',
        'contrat_id',
        'nom', // ✅ Ajouté pour les bénéficiaires
        'prenoms', // ✅ Ajouté pour les bénéficiaires
        'date_naissance', // ✅ Ajouté pour les bénéficiaires
        'sexe', // ✅ Ajouté pour les bénéficiaires
        'lien_parente',
        'est_principal',
        'statut',
        'date_debut_contrat',
        'date_fin_contrat',
        'photo_url',
    ];

    protected $casts = [
        'est_principal' => 'boolean',
        'date_debut_contrat' => 'date',
        'date_fin_contrat' => 'date',
        'date_naissance' => 'date', // ✅ Ajouté
        'lien_parente' => \App\Enums\LienParenteEnum::class,
        'statut' => \App\Enums\StatutAssureEnum::class,
        'sexe' => \App\Enums\SexeEnum::class, // ✅ Ajouté
    ];

    /**
     * Get the user that owns the assure.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the client that owns the assure.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the entreprise that owns the assure.
     */
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    /**
     * Get the principal assure for this beneficiary.
     */
    public function assurePrincipal()
    {
        return $this->belongsTo(Assure::class, 'assure_principal_id');
    }

    /**
     * Get the beneficiaries for this principal assure.
     */
    public function beneficiaires()
    {
        return $this->hasMany(Assure::class, 'assure_principal_id');
    }

    /**
     * Get the contrat for this assure.
     */
    public function contrat()
    {
        return $this->belongsTo(Contrat::class);
    }

    /**
     * Get the sinistres for this assure.
     */
    public function sinistres()
    {
        return $this->hasMany(Sinistre::class);
    }

    /**
     * Check if assure is principal.
     */
    public function isPrincipal()
    {
        return $this->est_principal;
    }

    /**
     * Check if assure is a beneficiary.
     */
    public function isBeneficiaire()
    {
        return !$this->est_principal;
    }

    /**
     * Check if assure is active.
     */
    public function isActive()
    {
        return $this->statut === 'actif';
    }

    /**
     * Check if assure is inactive.
     */
    public function isInactive()
    {
        return $this->statut === 'inactif';
    }

    /**
     * Check if assure is suspended.
     */
    public function isSuspended()
    {
        return $this->statut === 'suspendu';
    }

    /**
     * Get the assure's full name.
     */
    public function getFullNameAttribute()
    {
        if ($this->user) {
            return $this->user->full_name;
        }
        
        return 'Assuré #' . $this->id;
    }

    /**
     * Get the assure's type (principal or beneficiary).
     */
    public function getTypeAttribute()
    {
        return $this->est_principal ? 'Principal' : 'Bénéficiaire';
    }

    /**
     * Get the assure's source (client or entreprise).
     */
    public function getSourceAttribute()
    {
        if ($this->client) {
            return $this->client->isPhysique() ? 'Client Physique' : 'Client Moral';
        }
        
        if ($this->entreprise) {
            return 'Employé Entreprise';
        }
        
        return 'Inconnu';
    }
}
