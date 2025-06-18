<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Compagnie extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nom',
        'adresse',
        'email',
        'telephone',
        'site_web',
        'logo',
        'description',
        'est_actif',
    ];

    protected function casts(): array
    {
        return [
            'est_actif' => 'boolean',
        ];
    }

    // Exemple de relation : une compagnie a plusieurs gestionnaires
    public function gestionnaires()
    {
        return $this->hasMany(Gestionnaire::class);
    }

    // Exemple de scope pour les compagnies actives
    public function scopeActives($query)
    {
        return $query->where('est_actif', true);
    }
}