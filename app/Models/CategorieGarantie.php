<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategorieGarantie extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'libelle_categories',
        'description',
        'est_actif'
    ];

    protected function casts(): array
    {
        return [
            'est_actif' => 'boolean',
        ];
    }

    /**
     * Get the garanties for the categorie.
     */
    public function garanties()
    {
        return $this->hasMany(Garantie::class);
    }

    /**
     * Check if categorie is active.
     */
    public function isActive(): bool
    {
        return $this->est_actif;
    }

    /**
     * Scope to get only active categories.
     */
    public function scopeActive($query)
    {
        return $query->where('est_actif', true);
    }
}
