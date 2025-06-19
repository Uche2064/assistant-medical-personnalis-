<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conversation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id_1',
        'user_id_2',
        'dernier_message',
    ];

    // Relation vers le premier utilisateur
    public function utilisateur1()
    {
        return $this->belongsTo(User::class, 'user_id_1');
    }

    // Relation vers le second utilisateur
    public function utilisateur2()
    {
        return $this->belongsTo(User::class, 'user_id_2');
    }

    // Ajoute ici la relation messages() si tu as bien une table messages liée à conversation_id
    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}