<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'libelle',
        'type_donnee',
        'options',
        'destinataire',
        'obligatoire',
        'est_actif',
        'cree_par_id',
    ];

    protected $casts = [
        'options' => 'array',
        'obligatoire' => 'boolean',
        'est_actif' => 'boolean',
        'type_donnee' => \App\Enums\TypeDonneeEnum::class,
        'destinataire' => \App\Enums\TypeDemandeurEnum::class,
    ];

    /**
     * Get the personnel that created this question.
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
        return $this->hasMany(ReponseQuestionnaire::class);
    }

    /**
     * Scope to get active questions.
     */
    public function scopeActive($query)
    {
        return $query->where('est_actif', true);
    }

    /**
     * Scope to get questions by destinataire.
     */
    public function scopeByDestinataire($query, $destinataire)
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

    /**
     * Check if question is active.
     */
    public function isActive()
    {
        return $this->est_actif;
    }

    /**
     * Check if question is required.
     */
    public function isRequired()
    {
        return $this->obligatoire;
    }

    public function scopeForDestinataire($query, string $destinataire) {
        return $query->where('destinataire', $destinataire)->where('est_actif', true);
    }

    /**
     * Get the question type in French.
     */
    public function getTypeDonneeFrancaisAttribute()
    {
        return $this->type_donnee->getLabel();
    }

    /**
     * Get the destinataire in French.
     */
    public function getDestinataireFrancaisAttribute()
    {
        return $this->destinataire->getLabel();
    }
}
