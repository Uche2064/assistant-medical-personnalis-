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
        'question_id',
        'personne_id',
        'personne_type',
        'reponse_bool',
        'reponse_text',
        'reponse_decimal',
        'reponse_date',
        'reponse_fichier',
    ];

    protected function casts(): array
    {
        return [
            'reponse_bool' => 'boolean',
            'reponse_text' => 'string',
            'reponse_decimal' => 'decimal:2',
            'reponse_date' => 'date',
            'reponse_fichier' => 'string',
        ];
    }
    
    public function demandeAdhesion()
    {
        return $this->belongsTo(DemandesAdhesions::class);
    }

    public function personne()
    {
        return $this->morphTo();
    }

    
    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function isEmpty(): bool
    {
        return empty($this->reponses) || 
               (is_array($this->reponses) && empty(array_filter($this->reponses)));
    }
}
