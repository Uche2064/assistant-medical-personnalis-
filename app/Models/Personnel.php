<?php

namespace App\Models;

use App\Enums\TypePersonnelEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Personnel extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'type_personnel',
        'compagnie_id',
        'gestionnaire_id'
    ];

    protected function casts(): array
    {
        return [
            'type_personnel' => TypePersonnelEnum::class,
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function compagnie()
    {
        return $this->belongsTo(Compagnie::class);
    }

    public function demandesAdhesionValidees()
    {
        return $this->hasMany(DemandeAdhesion::class, 'valide_par_id');
    }


    public function demandesAdhesionCreees()
    {
        return $this->hasMany(DemandeAdhesion::class, 'fait_par');
    }


    public function questions()
    {
        return $this->hasMany(Question::class, 'cree_par_id');
    }


    public function facturesValideesMedecin()
    {
        return $this->hasMany(Facture::class, 'medecin_id');
    }

    public function facturesValideesTechnicien()
    {
        return $this->hasMany(Facture::class, 'technicien_id');
    }


    public function facturesAutorisees()
    {
        return $this->hasMany(Facture::class, 'comptable_id');
    }


    public function prestataires()
    {
        return $this->hasMany(Prestataire::class, 'medecin_controleur_id');
    }

    public function isMedecinControleur(): bool
    {
        return $this->type_personnel === TypePersonnelEnum::MEDECIN_CONTROLEUR;
    }


    public function isTechnicien(): bool
    {
        return $this->type_personnel === TypePersonnelEnum::TECHNICIEN;
    }


    public function isComptable(): bool
    {
        return $this->type_personnel === TypePersonnelEnum::COMPTABLE;
    }
}
