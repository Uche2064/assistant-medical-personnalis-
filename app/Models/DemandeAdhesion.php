<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\StatutDemandeAdhesionEnum;
use App\Enums\TypeDemandeurEnum;

class DemandeAdhesion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'demandes_adhesions';

    protected $fillable = [
        'user_id',
        'type_demandeur',
        'statut',
        'motif_rejet',
        'valide_par_id',
        'code_parainage',
        'valider_a',
    ];

    protected $casts = [
        'valider_a' => 'datetime',
        'statut' => StatutDemandeAdhesionEnum::class,
        'type_demandeur' => TypeDemandeurEnum::class,
    ];

    /**
     * Get the user that owns the demande adhesion.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the personnel that validated this demande.
     */
    public function validePar()
    {
        return $this->belongsTo(Personnel::class, 'valide_par_id');
    }

    /**
     * Get the reponses questionnaire for this demande.
     */
    public function reponsesQuestionnaire()
    {
        // Pour l'assuré principal
        return $this->hasMany(ReponseQuestionnaire::class, 'personne_id', 'user_id')
            ->where('personne_type', User::class);
    }

    /**
     * Get the entreprise associated with this demande (if type_demandeur is entreprise)
     */
    public function entreprise()
    {
        return $this->hasOneThrough(
            Entreprise::class,
            User::class,
            'id', // Clé étrangère sur users
            'user_id', // Clé étrangère sur entreprises
            'user_id', // Clé locale sur demandes_adhesions
            'id' // Clé locale sur users
        );
    }

    /**
     * Get the client associated with this demande
     */
    public function client()
    {
        return $this->hasOneThrough(
            Client::class,
            User::class,
            'id', // Clé étrangère sur users
            'user_id', // Clé étrangère sur clients
            'user_id', // Clé locale sur demandes_adhesions
            'id' // Clé locale sur users
        );
    }

    /**
     * Get the assures (employees) associated with this demande
     */
    public function assures()
    {
        return $this->hasManyThrough(
            Assure::class,
            User::class,
            'id', // Clé étrangère sur users
            'user_id', // Clé étrangère sur assures
            'user_id', // Clé locale sur demandes_adhesions
            'id' // Clé locale sur users
        );
    }

    /**
     * Get the beneficiaires associated with this demande
     */
    public function beneficiaires()
    {
        return $this->hasManyThrough(
            Assure::class,
            User::class,
            'id', // Clé étrangère sur users
            'user_id', // Clé étrangère sur assures
            'user_id', // Clé locale sur demandes_adhesions
            'id' // Clé locale sur users
        )->where('est_principal', false);
    }

    /**
     * Get the employes (assures principaux) associated with this demande
     */
    public function employes()
    {
        return $this->hasManyThrough(
            Assure::class,
            User::class,
            'id', // Clé étrangère sur users
            'user_id', // Clé étrangère sur assures
            'user_id', // Clé locale sur demandes_adhesions
            'id' // Clé locale sur users
        )->where('est_principal', true);
    }

    /**
     * Get all reponses questionnaire for this demande (including beneficiaires and employes)
     */
    public function allReponsesQuestionnaire()
    {
        return $this->hasMany(ReponseQuestionnaire::class, 'personne_id', 'user_id')
            ->orWhere(function($query) {
                $query->whereIn('personne_id', $this->assures()->pluck('assures.id'))
                    ->where('personne_type', Assure::class);
            });
    }

    /**
     * Check if demande is pending.
     */
    public function isPending()
    {
        return $this->statut === StatutDemandeAdhesionEnum::EN_ATTENTE;
    }

    /**
     * Check if demande is validated.
     */
    public function isValidated()
    {
        return $this->statut === StatutDemandeAdhesionEnum::VALIDEE;
    }

    /**
     * Check if demande is rejected.
     */
    public function isRejected()
    {
        return $this->statut === StatutDemandeAdhesionEnum::REJETEE;
    }

    /**
     * Validate the demande.
     */
    public function validate($valideParId = null)
    {
        $this->statut = StatutDemandeAdhesionEnum::VALIDEE;
        $this->valide_par_id = $valideParId;
        $this->valider_a = now();
        $this->save();
    }

    /**
     * Reject the demande.
     */
    public function reject($motifRejet, $valideParId = null)
    {
        $this->statut = StatutDemandeAdhesionEnum::REJETEE;
        $this->motif_rejet = $motifRejet;
        $this->valide_par_id = $valideParId;
        $this->valider_a = now();
        $this->save();
    }

    /**
     * Get the demandeur type in French.
     */
    public function getTypeDemandeurFrancaisAttribute()
    {
        return $this->type_demandeur->getLabel();
    }
} 