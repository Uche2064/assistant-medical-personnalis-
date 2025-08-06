<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contrat extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'type_contrat',
        'technicien_id',
        'prime_standard',
        'date_debut',
        'date_fin',
        'est_actif',
        'categories_garanties_standard',
    ];

    protected $casts = [
        'prime_standard' => 'decimal:2',
        'est_actif' => 'boolean',
        'type_contrat' => \App\Enums\TypeContratEnum::class,
        'categories_garanties_standard' => 'array',
        'date_debut' => 'date',
        'date_fin' => 'date',
    ];

    /**
     * Get the technicien that manages this contrat.
     */
    public function technicien()
    {
        return $this->belongsTo(Personnel::class, 'technicien_id');
    }

    /**
     * Get the assures for this contrat.
     */
    public function assures()
    {
        return $this->hasMany(Assure::class);
    }

    /**
     * Get the categories garanties for this contrat.
     */
    public function categoriesGaranties()
    {
        return $this->belongsToMany(CategorieGarantie::class, 'contrat_categorie_garantie')
                    ->withPivot('couverture')
                    ->withTimestamps();
    }

    /**
     * Generate a unique police number.
     */
    public static function generateNumeroPolice()
    {
        do {
            $numero = 'POL' . date('Y') . strtoupper(substr(md5(uniqid()), 0, 8));
        } while (self::where('numero_police', $numero)->exists());

        return $numero;
    }

    /**
     * Check if contrat is proposed.
     */
    public function isProposed()
    {
        return $this->statut === \App\Enums\StatutContratEnum::PROPOSE;
    }

    /**
     * Check if contrat is accepted.
     */
    public function isAccepted()
    {
        return $this->statut === \App\Enums\StatutContratEnum::ACCEPTE;
    }

    /**
     * Check if contrat is refused.
     */
    public function isRefused()
    {
        return $this->statut === \App\Enums\StatutContratEnum::REFUSE;
    }

    /**
     * Check if contrat is active.
     */
    public function isActive()
    {
        return $this->statut === \App\Enums\StatutContratEnum::ACTIF && $this->est_actif;
    }

    /**
     * Check if contrat is expired.
     */
    public function isExpired()
    {
        return $this->statut === \App\Enums\StatutContratEnum::EXPIRE || $this->date_fin < now();
    }

    /**
     * Check if contrat is cancelled.
     */
    public function isCancelled()
    {
        return $this->statut === \App\Enums\StatutContratEnum::RESILIE;
    }

    /**
     * Accept the contrat.
     */
    public function accept()
    {
        $this->statut = \App\Enums\StatutContratEnum::ACCEPTE;
        $this->est_actif = true;
        $this->save();
    }

    /**
     * Refuse the contrat.
     */
    public function refuse()
    {
        $this->statut = \App\Enums\StatutContratEnum::REFUSE;
        $this->est_actif = false;
        $this->save();
    }

    /**
     * Activate the contrat.
     */
    public function activate()
    {
        $this->statut = \App\Enums\StatutContratEnum::ACTIF;
        $this->est_actif = true;
        $this->save();
    }

    /**
     * Calculate the total prime with fees.
     */
    public function getPrimeTotaleAttribute()
    {
        return $this->prime_standard + ($this->prime_standard * $this->frais_gestion / 100);
    }

    /**
     * Calculate the commercial commission amount.
     */
    public function getCommissionAmountAttribute()
    {
        return $this->prime_standard * $this->commission_commercial / 100;
    }


    /**
     * Check if contrat is still valid.
     */
    public function isValid()
    {
        return $this->isActive() && $this->date_fin >= now();
    }
}
