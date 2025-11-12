<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReponseQuestion extends Model
{
    use HasFactory;

    protected $table = 'reponses_questions';

    protected $fillable = [
        'question_id',
        'demande_adhesion_id',
        'user_id',
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assure()
    {
        return $this->belongsTo(Assure::class);
    }
}


