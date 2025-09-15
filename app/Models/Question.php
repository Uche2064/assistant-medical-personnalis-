<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'libelle',
        'type_de_donnee',
        'options',
        'destinataire',
        'est_obligatoire',
        'est_active',
        'cree_par_id',
    ];

    protected $casts = [
        'options' => 'array',
        'est_obligatoire' => 'boolean',
        'est_active' => 'boolean',
        'type_de_donnee' => \App\Enums\TypeDonneeEnum::class,
        'destinataire' => \App\Enums\TypeDemandeurEnum::class,
    ];

    /**
     * Get the personnel that created this question.
     */
    public function creeePar()
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
        return $query->where('est_active', true);
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
        return $query->where('est_obligatoire', true);
    }

    /**
     * Check if question is active.
     */
    public function isActive()
    {
        return $this->est_active;
    }

    /**
     * Check if question is required.
     */
    public function isRequired()
    {
        return $this->est_obligatoire;
    }

    public function scopeForDestinataire($query, string $destinataire) {
        return $query->where('destinataire', $destinataire)->where('est_active', true);
    }

    /**
     * Get the question type in French.
     */
    public function getTypeDonneeFrancaisAttribute()
    {
        return $this->type_de_donnee->getLabel();
    }

    /**
     * Get the destinataire in French.
     */
    public function getDestinataireFrancaisAttribute()
    {
        return $this->destinataire->getLabel();
    }
}
