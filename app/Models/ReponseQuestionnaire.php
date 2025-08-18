<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReponseQuestionnaire extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'reponses_questionnaire';

    protected $fillable = [
        'question_id',
        'personne_type',
        'personne_id',
        'reponse_text',
        'reponse_bool',
        'reponse_decimal',
        'reponse_date',
        'reponse_fichier',
        '',
        'demande_adhesion_id'
    ];

    protected $casts = [
        'reponse_bool' => 'boolean',
        'reponse_decimal' => 'decimal:2',
        'reponse_date' => 'date',
    ];

    

    /**
     * Get the question that owns the reponse.
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Get the polymorphic personne relationship.
     */
    public function personne()
    {
        return $this->morphTo();
    }

    /**
     * Get the reponse value based on the question type.
     */
    public function getReponseValueAttribute()
    {
        switch ($this->question->type_donnee) {
            case 'text':
                return $this->reponse_text;
            case 'number':
                return $this->reponse_decimal;
            case 'boolean':
                return $this->reponse_bool;
            case 'date':
                return $this->reponse_date;
            case 'file':
                return $this->reponse_fichier;
            default:
                return $this->reponse_text;
        }
    }

    /**
     * Set the reponse value based on the question type.
     */
    public function setReponseValueAttribute($value)
    {
        switch ($this->question->type_donnee) {
            case 'text':
                $this->reponse_text = $value;
                break;
            case 'number':
                $this->reponse_decimal = $value;
                break;
            case 'boolean':
                $this->reponse_bool = $value;
                break;
            case 'date':
                $this->reponse_date = $value;
                break;
            case 'file':
                $this->reponse_fichier = $value;
                break;
            default:
                $this->reponse_text = $value;
        }
    }
} 