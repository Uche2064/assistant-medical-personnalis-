<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LienInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'jeton',
        'expire_a',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
