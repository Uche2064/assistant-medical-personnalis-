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

}
