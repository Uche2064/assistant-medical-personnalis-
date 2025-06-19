<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Gestionnaire extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'compagnie_id',
    ];

    // Relation vers l'utilisateur
    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relation vers la compagnie
    public function compagnie()
    {
        return $this->belongsTo(Compagnie::class);
    }

    // Relation vers les clients gérés
    public function clients()
    {
        return $this->hasMany(Client::class);
    }
}