<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeContrat extends Model
{
    use HasFactory;

    protected $table = 'types_contrats';

    protected $fillable = [
        'libelle',
        'prime_standard',
        'est_actif',
        'technicien_id',
    ];

    public function technicien()
    {
        return $this->belongsTo(Personnel::class, 'technicien_id');
    }

    public function categoriesGaranties()
    {
        return $this->belongsToMany(CategorieGarantie::class, 'contrat_categorie_garantie')
            ->withPivot(['couverture','frais_gestion'])
            ->withTimestamps();
    }
}


