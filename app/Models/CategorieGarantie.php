<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategorieGarantie extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'libelle',
        'description',
        'medecin_controleur_id',
    ];

    /**
     * Get the medecin controleur that manages this categorie.
     */
    public function medecinControleur()
    {
        return $this->belongsTo(Personnel::class, 'medecin_controleur_id');
    }

    /**
     * Get the garanties for this categorie.
     */
    public function garanties()
    {
        return $this->hasMany(Garantie::class);
    }

    /**
     * Get the contrats that use this categorie.
     */
    public function contrats()
    {
        return $this->belongsToMany(Contrat::class, 'contrat_categorie_garantie')
                    ->withPivot('couverture')
                    ->withTimestamps();
    }

    /**
     * Check if categorie is active.
     */
    public function isActive()
    {
        return $this->garanties()->where('est_actif', true)->exists();
    }

    /**
     * Get the total coverage for this categorie.
     */
    public function getTotalCoverageAttribute()
    {
        return $this->garanties()->sum('plafond');
    }
} 