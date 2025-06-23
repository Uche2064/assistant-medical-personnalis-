<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReponsesQuestionnaire extends Model
{
    use HasFactory, SoftDeletes;

    // Définir explicitement le nom de la table
    protected $table = 'reponses_questionnaire';

    protected $fillable = [
        'demande_adhesion_id',
        'reponses',
        'est_validee'
    ];

    protected function casts(): array
    {
        return [
            'reponses' => 'json',
            'est_validee' => 'boolean'
        ];
    }

    /**
     * Get the demande adhesion that owns the reponse.
     */
    public function demandeAdhesion()
    {
        return $this->belongsTo(DemandeAdhesion::class);
    }

    /**
     * Check if response is empty.
     */
    public function isEmpty(): bool
    {
        return empty($this->reponses) || 
               (is_array($this->reponses) && empty(array_filter($this->reponses)));
    }
}
