<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'conversation_id',
        'expediteur_id',
        'contenu',
        'lu_a',
    ];

    protected function casts(): array
    {
        return [
            'lu_a' => 'datetime',
        ];
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function expediteur()
    {
        return $this->belongsTo(User::class, 'expediteur_id');
    }
}