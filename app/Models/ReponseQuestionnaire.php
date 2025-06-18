<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReponseQuestionnaire extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'question_id',
        'demande_adhesion_id',
        'reponse',
        'est_valide'
    ];

    protected function casts(): array
    {
        return [
            'reponse' => 'json',
            'est_valide' => 'boolean'
        ];
    }

    /**
     * Get the question that owns the reponse.
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Get the demande adhesion that owns the reponse.
     */
    public function demandeAdhesion()
    {
        return $this->belongsTo(DemandeAdhesion::class);
    }

    /**
     * Get the response value based on question type.
     */
    public function getFormattedReponse()
    {
        if (!$this->question) {
            return $this->reponse;
        }

        switch ($this->question->type_donnees) {
            case 'date':
                return is_string($this->reponse) ? \Carbon\Carbon::parse($this->reponse) : $this->reponse;
            case 'bool':
                return (bool) $this->reponse;
            case 'nombre':
                return is_numeric($this->reponse) ? (float) $this->reponse : $this->reponse;
            case 'fichier':
                return is_array($this->reponse) ? $this->reponse : [$this->reponse];
            default:
                return $this->reponse;
        }
    }

    /**
     * Check if response is empty.
     */
    public function isEmpty(): bool
    {
        return empty($this->reponse) || 
               (is_array($this->reponse) && empty(array_filter($this->reponse)));
    }
}
