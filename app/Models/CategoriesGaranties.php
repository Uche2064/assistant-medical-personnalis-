<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategoriesGaranties extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'libelle',
        'description',
        'medecin_controleur_id'
    ];

    /**
     * Get the garanties for the categorie.
     */
     public function garanties()
    {
        return $this->hasMany(Garantie::class, 'categorie_garantie_id');
    }

    public function contrats()
    {
        return $this->belongsToMany(Contrat::class, 'contrat_categorie_garantie')
                    ->withPivot('couverture')
                    ->withTimestamps();
    }

    public function medecinControleur()
    {
        return $this->belongsTo(Personnel::class, 'medecin_controleur_id');
    }
}
