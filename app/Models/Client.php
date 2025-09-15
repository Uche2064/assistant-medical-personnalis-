<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type_client',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function demandesAdhesions()
    {
        return $this->hasMany(DemandeAdhesion::class);
    }

    public function assures()
    {
        return $this->hasMany(Assure::class);
    }

    public function clientsContrats()
    {
        return $this->hasMany(ClientContrat::class);
    }
}


