<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContratCategorieGarantie extends Model
{
    protected $fillable = [
        'contrat_id',
        'categorie_garantie_id',
        'couverture',
    ];

    public function contrat()
    {
        return $this->belongsTo(Contrat::class, 'contrat_id');
    }

    public function categorieGarantie()
    {
        return $this->belongsTo(CategoriesGaranties::class, 'categorie_garantie_id');
    }
}
