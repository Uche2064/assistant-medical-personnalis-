<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReponseQuestionnaire extends Model
{
    use HasFactory;

    // Deprecated model: kept for backward compatibility. New model is ReponseQuestion using table reponses_questions
    protected $table = 'reponses_questions';

    protected $fillable = [
        'question_id',
        'demande_adhesion_id',
        'reponse',
        'date_reponse',
    ];

    protected $casts = [
        'date_reponse' => 'date',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function demandeAdhesion()
    {
        return $this->belongsTo(DemandeAdhesion::class);
    }
} 
