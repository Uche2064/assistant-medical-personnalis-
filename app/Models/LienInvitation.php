<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LienInvitation extends Model
{
    use HasFactory;

    protected $table = 'liens_invitations';

    protected $fillable = [
        'client_id',
        'jeton',
        'expire_a',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

     /**
     * Génère un jeton simple.
     *
     * @return string
     */
    public static function genererToken(): string
    {
        // Génère une chaîne unique basée sur l'heure actuelle en microsecondes.
        return uniqid('token_', true);
    }
}
