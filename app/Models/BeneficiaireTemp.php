<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BeneficiaireTemp extends Model
{
    use SoftDeletes;
    protected $table = 'beneficiaires_temp';

    protected $fillable = ['employe_temp_id', 'nom', 'prenoms', 'date_naissance', 'sexe', 'lien_parente'];

    public function employe()
    {
        return $this->belongsTo(EmployesTemp::class, 'employe_temp_id');
    }

    public function reponses()
    {
        return $this->morphMany(ReponsesQuestionnaire::class, 'personne');
    }
}
