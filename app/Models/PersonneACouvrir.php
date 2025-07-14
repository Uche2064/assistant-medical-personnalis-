<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonneACouvrir extends Model
{

    protected $fillable = [
        'nom',
        'prenoms',
        'demande_adhesion_id',
        'lien_parente',
        'est_principal',
        'email',
        'contact',
        'date_naissance',
        'adresse',
        'sexe',
        'profession',
        'nombre_de_beneficiaires',
        'photo_url'
    ];



    public function reponsesQuestionnaire()
    {
        return $this->morphMany(ReponsesQuestionnaire::class, 'personne');
    }
}
