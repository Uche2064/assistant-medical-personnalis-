<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReponsesQuestionnaire extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = "reponses_questionnaire";

    protected $fillable = [
        'demande_adhesion_id',
        'reponses',
    ];

    protected function casts(): array
    {
        return [
            'reponses' => 'json',
        ];
    }
    
    public function demandeAdhesion()
    {
        return $this->belongsTo(DemandeAdhesion::class);
    }

    public function isEmpty(): bool
    {
        return empty($this->reponses) || 
               (is_array($this->reponses) && empty(array_filter($this->reponses)));
    }
}
