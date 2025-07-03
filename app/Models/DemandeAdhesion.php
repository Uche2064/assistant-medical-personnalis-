<?php

namespace App\Models;

use App\Enums\SexeEnum;
use App\Enums\StatutValidationEnum;
use App\Enums\TypeDemandeurEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DemandeAdhesion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'demandes_adhesions';

    protected $fillable = [
        'nom_demandeur',
        'raison_sociale',
        'prenoms_demandeur',
        'date_naissance',
        'email',
        'contact',
        'adresse',
        'type_demande',
        'profession',
        'statut',
        'valide_par_id',
        'fait_par',
        'sexe',
        'valider_a',
        'motif_rejet',
    ];

    protected function casts(): array
    {
        return [
            'type_demande' => TypeDemandeurEnum::class,
            'statut' => StatutValidationEnum::class,
            'sexe' => SexeEnum::class,
            'valider_a' => 'datetime',
            'adresse' => 'json',
            'date_naissance'=> 'date'
        ];
    }
  
    public function validePar()
    {
        return $this->belongsTo(Personnel::class, 'valide_par_id');
    }
    
    public function faitPar()
    {
        return $this->belongsTo(Personnel::class, 'fait_par');
    }
   
    public function reponsesQuestionnaire()
    {
        return $this->hasMany(ReponsesQuestionnaire::class);
    }

    public function isPending(): bool
    {
        return $this->statut === StatutValidationEnum::EN_ATTENTE;
    }
 
    public function isValidated(): bool
    {
        return $this->statut === StatutValidationEnum::VALIDE;
    }

    public function isRejected(): bool
    {
        return $this->statut === StatutValidationEnum::REJETE;
    }

    public function validate(Personnel $personnel): void
    {
        $this->update([
            'statut' => StatutValidationEnum::VALIDE,
            'valide_par_id' => $personnel->id,
            'valider_a' => now()
        ]);
    }

    public function reject(Personnel $personnel, String $motif_rejet): void
    {
        $this->update([
            'statut' => StatutValidationEnum::REJETE,
            'valide_par_id' => $personnel->id,
            'valider_a' => now(),
            'motif_rejet' => $motif_rejet,
            'deleted_at' => now()
        ]);
    }

    public function scopePending($query)
    {
        return $query->where('statut', StatutValidationEnum::EN_ATTENTE);
    }

    public function scopeValidated($query)
    {
        return $query->where('statut', StatutValidationEnum::VALIDE);
    }
   
    public function scopeRejected($query)
    {
        return $query->where('statut', StatutValidationEnum::REJETE);
    }
}
