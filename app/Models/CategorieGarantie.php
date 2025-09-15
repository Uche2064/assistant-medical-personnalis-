<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategorieGarantie extends Model
{
    use HasFactory;

    protected $table = 'categories_garanties';

    protected $fillable = [
        'libelle',
        'description',
        'medecin_controleur_id',
        'est_active',
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
        return $this->hasMany(Garantie::class, 'categorie_garantie_id');
    }
    

    /**
     * Get the contrats that use this categorie.
     */
    // Pivot is between TypeContrat and CategorieGarantie
    public function typesContrats()
    {
        return $this->belongsToMany(TypeContrat::class, 'contrat_categorie_garantie')
                    ->withPivot(['couverture','frais_gestion'])
                    ->withTimestamps();
    }

    /**
     * Check if categorie is active.
     */
    public function isActive()
    {
        return (bool) $this->est_active;
    }

    /**
     * Get the total coverage for this categorie.
     */
    public function getTotalCoverageAttribute()
    {
        return $this->garanties()->sum('plafond');
    }
} 
