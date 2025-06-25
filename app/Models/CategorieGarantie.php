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
    ];

    /**
     * Get the garanties for the categorie.
     */
    public function garanties()
    {
        return $this->hasMany(Garantie::class);
    }
}
