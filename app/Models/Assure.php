<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class Assure extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'entreprise_id',
        'assure_principal_id',
        'email',
        'contrat_id',
        'nom', // ✅ Ajouté pour les bénéficiaires
        'prenoms', // ✅ Ajouté pour les bénéficiaires
        'date_naissance', // ✅ Ajouté pour les bénéficiaires
        'sexe', // ✅ Ajouté pour les bénéficiaires
        'lien_parente',
        'est_principal',
        'profession',
        'contact',
        'photo',
        'demande_adhesion_id',
    ];

    protected $casts = [
        'est_principal' => 'boolean',
        'date_naissance' => 'date', // ✅ Ajouté
        'lien_parente' => \App\Enums\LienParenteEnum::class,
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

    public function reponsesQuestionnaire()
    {
        return $this->hasMany(ReponseQuestionnaire::class, 'personne_id');
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

    public function hasContratActif(): bool
    {
        $contratAssocie = $this->getContratAssocie();
        Log::info('Contrat associé : ' . $contratAssocie->statut->value);

        if (!$contratAssocie) {
            return false; // Pas de contrat associé
        }

        // On suppose que 'statut' indique l'état, et 'actif' signifie contrat en cours
        return $contratAssocie->statut->value === 'actif' || $contratAssocie->statut === \App\Enums\StatutContratEnum::ACTIF->value;
    }


    public function getContratAssocie()
    {
        if ($this->est_principal && $this->entreprise_id) {
            // Assuré principal : on récupère le client contrat lié directement
            return ClientContrat::with('contrat.categoriesGaranties.garanties')
                ->where('user_id', $this->entreprise->user->id)
                ->where('statut', 'actif')
                ->first();
        }


        // Sinon on récupère le contrat de l'assuré principal (s'il y a un)
        if ($this->assure_principal_id) {
            $principal = self::find($this->assure_principal_id);
            if ($principal) {
                return ClientContrat::with('contrat.categoriesGaranties.garanties')
                    ->where('user_id', $principal->user_id)
                    ->where('statut', 'actif')
                    ->first();
            }
        }

        return null;
    }
}
