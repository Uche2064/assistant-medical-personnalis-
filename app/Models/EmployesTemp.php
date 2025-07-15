<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployesTemp extends Model
{
     use SoftDeletes;

     protected $table = 'employes_temp';
    protected $fillable = ['prospect_id', 'nom', 'prenoms', 'date_naissance', 'sexe', 'contact','email'];

    public function prospect()
    {
        return $this->belongsTo(Prospect::class);
    }

    public function beneficiaires()
    {
        return $this->hasMany(BeneficiaireTemp::class, 'employe_temp_id');
    }

    public function reponses()
    {
        return $this->morphMany(ReponsesQuestionnaire::class, 'personne');
    }
}
