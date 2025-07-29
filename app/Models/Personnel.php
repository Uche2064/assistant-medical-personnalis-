<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Personnel extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'nom',
        'prenoms',
        'sexe',
        'date_naissance',
        'code_parainage',
        'gestionnaire_id',
    ];

    protected $casts = [
        'date_naissance' => 'date',
    ];

    /**
     * Get the user that owns the personnel.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the gestionnaire (manager) for this personnel.
     */
    public function gestionnaire()
    {
        return $this->belongsTo(Personnel::class, 'gestionnaire_id');
    }

    /**
     * Get the personnels managed by this personnel.
     */
    public function personnels()
    {
        return $this->hasMany(Personnel::class, 'gestionnaire_id');
    }

    /**
     * Get the clients managed by this commercial.
     */
    public function clients()
    {
        return $this->hasMany(Client::class, 'commercial_id');
    }

    /**
     * Get the contrats managed by this technicien.
     */
    public function contrats()
    {
        return $this->hasMany(Contrat::class, 'technicien_id');
    }

    /**
     * Get the categories garanties managed by this medecin controleur.
     */
    public function categoriesGaranties()
    {
        return $this->hasMany(CategorieGarantie::class, 'medecin_controleur_id');
    }

    /**
     * Get the garanties managed by this medecin controleur.
     */
    public function garanties()
    {
        return $this->hasMany(Garantie::class, 'medecin_controleur_id');
    }

    /**
     * Get the prestataires managed by this medecin controleur.
     */
    public function prestataires()
    {
        return $this->hasMany(Prestataire::class, 'medecin_controleur_id');
    }

    /**
     * Get the demandes adhesions validated by this personnel.
     */
    public function demandesAdhesionsValidees()
    {
        return $this->hasMany(DemandeAdhesion::class, 'valide_par_id');
    }

    /**
     * Get the factures validated by this technicien.
     */
    public function facturesValideesTechnicien()
    {
        return $this->hasMany(Facture::class, 'technicien_id');
    }

    /**
     * Get the factures validated by this medecin.
     */
    public function facturesValideesMedecin()
    {
        return $this->hasMany(Facture::class, 'medecin_id');
    }

    /**
     * Get the factures authorized by this comptable.
     */
    public function facturesAutoriseesComptable()
    {
        return $this->hasMany(Facture::class, 'comptable_id');
    }

    /**
     * Get the questions created by this personnel.
     */
    public function questions()
    {
        return $this->hasMany(Question::class, 'cree_par_id');
    }

    /**
     * Generate a unique parrainage code.
     */
    public static function genererCodeParainage()
    {
        do {
            $code = 'PAR' . strtoupper(substr(md5(uniqid()), 0, 6));
        } while (self::where('code_parainage', $code)->exists());

        return $code;
    }

    /**
     * Get the full name of the personnel.
     */
    public function getFullNameAttribute()
    {
        return $this->nom . ' ' . $this->prenoms;
    }

    /**
     * Check if personnel is a gestionnaire.
     */
    public function isGestionnaire()
    {
        return $this->personnels()->exists();
    }

    /**
     * Check if personnel is a commercial.
     */
    public function isCommercial()
    {
        return $this->clients()->exists();
    }

    /**
     * Check if personnel is a technicien.
     */
    public function isTechnicien()
    {
        return $this->contrats()->exists();
    }

    /**
     * Check if personnel is a medecin controleur.
     */
    public function isMedecinControleur()
    {
        return $this->categoriesGaranties()->exists() || 
               $this->garanties()->exists() || 
               $this->prestataires()->exists();
    }

    /**
     * Check if personnel is a comptable.
     */
    public function isComptable()
    {
        return $this->facturesAutoriseesComptable()->exists();
    }
}
