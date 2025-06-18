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
        'utilisateur_id',
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

    /**
     * Get the user that owns the personnel.
     */
    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'utilisateur_id');
    }

    /**
     * Get the compagnie that owns the personnel.
     */
    public function compagnie()
    {
        return $this->belongsTo(Compagnie::class);
    }

    /**
     * Get the demandes d'adhesion validated by this personnel.
     */
    public function demandesAdhesionValidees()
    {
        return $this->hasMany(DemandeAdhesion::class, 'valide_par_id');
    }

    /**
     * Get the demandes d'adhesion created by this personnel.
     */
    public function demandesAdhesionCreees()
    {
        return $this->hasMany(DemandeAdhesion::class, 'fait_par');
    }

    /**
     * Get the questions created by this personnel.
     */
    public function questions()
    {
        return $this->hasMany(Question::class, 'cree_par_id');
    }

    /**
     * Get the factures validated by this medecin controleur.
     */
    public function facturesValideesMedecin()
    {
        return $this->hasMany(Facture::class, 'medecin_id');
    }

    /**
     * Get the factures validated by this technicien.
     */
    public function facturesValideesTechnicien()
    {
        return $this->hasMany(Facture::class, 'technicien_id');
    }

    /**
     * Get the factures authorized by this comptable.
     */
    public function facturesAutorisees()
    {
        return $this->hasMany(Facture::class, 'comptable_id');
    }

    /**
     * Get the prestataires controlled by this medecin controleur.
     */
    public function prestataires()
    {
        return $this->hasMany(Prestataire::class, 'medecin_controleur_id');
    }

    /**
     * Check if personnel is a medecin controleur.
     */
    public function isMedecinControleur(): bool
    {
        return $this->type_personnel === TypePersonnelEnum::MEDECIN_CONTROLEUR;
    }

    /**
     * Check if personnel is a technicien.
     */
    public function isTechnicien(): bool
    {
        return $this->type_personnel === TypePersonnelEnum::TECHNICIEN;
    }

    /**
     * Check if personnel is a comptable.
     */
    public function isComptable(): bool
    {
        return $this->type_personnel === TypePersonnelEnum::COMPTABLE;
    }

}
