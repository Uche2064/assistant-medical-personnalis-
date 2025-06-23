<?php

namespace App\Models;

use App\Enums\TypeDonneesEnum;
use App\Enums\DestinataireEnum;
use App\Enums\TypeDemandeurEnum;
use App\Enums\TypeDonneeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'libelle',
        'type_donnees',
        'destinataire',
        'obligatoire',
        'est_actif',
        'cree_par_id',
        'option'
    ];

    protected function casts(): array
    {
        return [
            'type_donnees' => TypeDonneeEnum::class,
            'destinataire' => TypeDemandeurEnum::class,
            'obligatoire' => 'boolean',
            'est_actif' => 'boolean',
        ];
    }

    /**
     * Get the personnel who created this question.
     */
    public function creePar()
    {
        return $this->belongsTo(Personnel::class, 'cree_par_id');
    }

    /**
     * Get the reponses for this question.
     */
    public function reponses()
    {
        return $this->hasMany(ReponsesQuestionnaire::class);
    }

    /**
     * Check if question is active.
     */
    public function isActive(): bool
    {
        return $this->est_actif;
    }

    /**
     * Check if question is required.
     */
    public function isRequired(): bool
    {
        return $this->obligatoire;
    }

    /**
     * Scope to get only active questions.
     */
    public function scopeActive($query)
    {
        return $query->where('est_actif', true);
    }

    /**
     * Scope to get questions for a specific destinataire.
     */
    public function scopeForDestinataire($query, TypeDemandeurEnum $destinataire)
    {
        return $query->where('destinataire', $destinataire);
    }

    /**
     * Scope to get required questions.
     */
    public function scopeRequired($query)
    {
        return $query->where('obligatoire', true);
    }
}
