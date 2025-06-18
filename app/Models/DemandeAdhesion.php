<?php

namespace App\Models;

use App\Enums\StatutValidationEnum;
use App\Enums\TypeDemandeurEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DemandeAdhesion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'demandes_adhesions';

    protected $fillable = [
        'nom',
        'raison_sociale',
        'prenoms',
        'email',
        'contact',
        'type_demande',
        'statut',
        'valide_par_id',
        'fait_par',
        'valider_a'
    ];

    protected function casts(): array
    {
        return [
            'type_demande' => TypeDemandeurEnum::class,
            'statut' => StatutValidationEnum::class,
            'valider_a' => 'datetime',
        ];
    }

    /**
     * Get the personnel who validated this demande.
     */
    public function validePar()
    {
        return $this->belongsTo(Personnel::class, 'valide_par_id');
    }

    /**
     * Get the personnel who created this demande.
     */
    public function faitPar()
    {
        return $this->belongsTo(Personnel::class, 'fait_par');
    }

    /**
     * Get the reponses questionnaire for this demande.
     */
    public function reponsesQuestionnaire()
    {
        return $this->hasMany(ReponseQuestionnaire::class);
    }

    /**
     * Check if demande is pending.
     */
    public function isPending(): bool
    {
        return $this->statut === StatutValidationEnum::EN_ATTENTE;
    }

    /**
     * Check if demande is validated.
     */
    public function isValidated(): bool
    {
        return $this->statut === StatutValidationEnum::VALIDE;
    }

    /**
     * Check if demande is rejected.
     */
    public function isRejected(): bool
    {
        return $this->statut === StatutValidationEnum::REJETE;
    }

    /**
     * Validate the demande.
     */
    public function validate(Personnel $personnel): void
    {
        $this->update([
            'statut' => StatutValidationEnum::VALIDE,
            'valide_par_id' => $personnel->id,
            'valider_a' => now()
        ]);
    }

    /**
     * Reject the demande.
     */
    public function reject(Personnel $personnel): void
    {
        $this->update([
            'statut' => StatutValidationEnum::REJETE,
            'valide_par_id' => $personnel->id,
            'valider_a' => now()
        ]);
    }

    /**
     * Scope to get pending demandes.
     */
    public function scopePending($query)
    {
        return $query->where('statut', StatutValidationEnum::EN_ATTENTE);
    }

    /**
     * Scope to get validated demandes.
     */
    public function scopeValidated($query)
    {
        return $query->where('statut', StatutValidationEnum::VALIDE);
    }

    /**
     * Scope to get rejected demandes.
     */
    public function scopeRejected($query)
    {
        return $query->where('statut', StatutValidationEnum::REJETE);
    }
}
