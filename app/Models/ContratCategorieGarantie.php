<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContratCategorieGarantie extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'contrat_categorie_garantie';

    protected $fillable = [
        'contrat_id',
        'categorie_garantie_id',
        'couverture',
    ];

    public function contrat()
    {
        return $this->belongsTo(Contrat::class);
    }

    public function categorieGarantie()
    {
        return $this->belongsTo(CategorieGarantie::class);
    }

    
}
