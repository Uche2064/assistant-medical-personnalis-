<?php

namespace App\Models;

use App\Enums\SexeEnum;
use App\Enums\StatutValidationEnum;
use App\Enums\TypeDemandeurEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DemandesAdhesions extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'type_demandeur', 'statut', 'motif_rejet',
        'prospect_id', 'valide_par_id', 'code_parainage', 'valider_a'
    ];

    protected $casts = [
        'valider_a' => 'datetime',
    ];

    
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
        return $this->statut === StatutValidationEnum::VALIDEE;
    }

    public function isRejected(): bool
    {
        return $this->statut === StatutValidationEnum::REJETEE;
    }

    public function validate(Personnel $personnel): void
    {
        $this->update([
            'statut' => StatutValidationEnum::VALIDEE,
            'valide_par_id' => $personnel->id,
            'valider_a' => now()
        ]);
    }

    public function reject(User $personnel, String $motif_rejet): void
    {
        $this->update([
            'statut' => StatutValidationEnum::REJETEE,
            'motif_rejet' => $motif_rejet,
            'valide_par_id' => $personnel->id,
            'valider_a' => now(),
        ]);
    }

    public function prospect()
    {
        return $this->belongsTo(Prospect::class, 'prospect_id');
    }
    
    public function scopePending($query)
    {
        return $query->where('statut', StatutValidationEnum::EN_ATTENTE);
    }

    public function scopeValidated($query)
    {
        return $query->where('statut', StatutValidationEnum::VALIDEE);
    }
   
    public function scopeRejected($query)
    {
        return $query->where('statut', StatutValidationEnum::REJETEE);
    }
}
