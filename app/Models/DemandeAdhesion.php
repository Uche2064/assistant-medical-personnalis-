<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\StatutDemandeAdhesionEnum;
use App\Enums\TypeDemandeurEnum;
use Illuminate\Support\Facades\Log;

class DemandeAdhesion extends Model
{
    use HasFactory;

    protected $table = 'demandes_adhesions';

    protected $fillable = [
        'client_id',
        'type_demandeur',
        'statut',
        'motif_rejet',
        'valide_par_id',
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
    public function client()
    {
        return $this->belongsTo(Client::class);
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
    public function reponsesQuestions()
    {
        return $this->hasMany(ReponseQuestion::class, 'demande_adhesion_id');
    }


    /**
     * Get the assures (employees) associated with this demande
     */
    public function assures()
    {
        return $this->hasMany(Assure::class, 'client_id', 'client_id');
    }

    /**
     * Get the employes (assures principaux) associated with this demande
     */
    public function employes()
    {
        return $this->hasMany(Assure::class, 'client_id', 'client_id')->where('est_principal', true);
    }

    /**
     * Get all reponses questionnaire for this demande (including beneficiaires and employes)
     */
    // With new schema, responses are tied directly via demande_adhesion_id
    public function allReponsesQuestions()
    {
        return $this->reponsesQuestions();
    }

    /**
     * Get the propositions de contrat for this demande.
     */
    public function propositionsContrat()
    {
        return $this->hasMany(PropositionContrat::class, 'demande_adhesion_id');
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
