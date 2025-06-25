<?php

namespace App\Models;

use App\Enums\TypeDonneesEnum;
use App\Enums\DestinataireEnum;
use App\Enums\TypeDemandeurEnum;
use App\Enums\TypeDonneeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'libelle',
        'type_donnees',
        'destinataire',
        'obligatoire',
        'est_actif',
        'cree_par_id',
        'options'
    ];

    protected function casts(): array
    {
        return [
            'type_donnees' => TypeDonneeEnum::class,
            'destinataire' => TypeDemandeurEnum::class,
            'obligatoire' => 'boolean',
            'est_actif' => 'boolean',
            'options' => 'json'
        ];
    }


    public function creePar()
    {
        return $this->belongsTo(Personnel::class, 'cree_par_id');
    }


    public function reponses()
    {
        return $this->hasMany(ReponsesQuestionnaire::class);
    }

    
    public function isActive(): bool
    {
        return $this->est_actif;
    }

   
    public function isRequired(): bool
    {
        return $this->obligatoire;
    }

   
    public function scopeActive($query)
    {
        return $query->where('est_actif', true);
    }


    public function scopeForDestinataire($query, TypeDemandeurEnum $destinataire)
    {
        return $query->where('destinataire', $destinataire);
    }

    public function scopeRequired($query)
    {
        return $query->where('obligatoire', true);
    }
}
