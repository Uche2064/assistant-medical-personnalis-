<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prospect extends Model
{
 use HasFactory, SoftDeletes;

    protected $fillable = [
        'nom', 'prenoms', 'email', 'contact', 'date_naissance',
        'adresse', 'photo_url', 'nombre_de_beneficiaires',
        'sexe', 'profession', 'raison_sociale',
        'type_prospect', 'user_id', 'client_id'
    ];

    protected $casts = [
        'date_naissance' => 'date',
        'nombre_de_beneficiaires' => 'integer',
    ];

    public function demande()
    {
        return $this->hasOne(DemandesAdhesions::class);
    }

}
