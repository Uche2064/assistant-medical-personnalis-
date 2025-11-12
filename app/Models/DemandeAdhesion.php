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
        'user_id',
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
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the client associated with this demande via user.
     */
    public function client()
    {
        return $this->hasOneThrough(
            Client::class,
            User::class,
            'id', // Foreign key on users table
            'user_id', // Foreign key on clients table
            'user_id', // Local key on demandes_adhesions table
            'id' // Local key on users table
        );
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


    // /**
    //  * Get all reponses for this demande grouped by assure
    //  */
    // public function reponsesParAssure()
    // {
    //     return $this->reponsesQuestions()
    //                 ->with(['assure.user.personne', 'question'])
    //                 ->get()
    //                 ->groupBy('assure_id');
    // }

    /**
     * Get reponses for the principal assure only (via user_id)
     */
    public function reponsesAssurePrincipal()
    {
        return $this->reponsesQuestions()
                    ->where('user_id', $this->user_id)
                    ->with(['question'])
                    ->get();
    }

    /**
     * Get reponses for the principal assure only (relation for eager loading)
     */
    public function reponsesAssurePrincipalRelation()
    {
        return $this->hasMany(ReponseQuestion::class, 'demande_adhesion_id')
                    ->whereColumn('user_id', 'demandes_adhesions.user_id');
    }

    /**
     * Get reponses for a specific user in this demande
     */
    public function reponsesParUtilisateur($userId)
    {
        return $this->reponsesQuestions()
                    ->where('user_id', $userId)
                    ->with(['question'])
                    ->get();
    }

    /**
     * Get reponses for a specific user (relation for eager loading)
     */
    public function reponsesParUtilisateurRelation($userId)
    {
        return $this->hasMany(ReponseQuestion::class, 'demande_adhesion_id')
                    ->where('user_id', $userId);
    }

    /**
     * Get reponses for beneficiaries only
     */
    public function reponsesBeneficiaires()
    {
        return $this->reponsesQuestions()
                    ->whereHas('assure', function ($query) {
                        $query->where('est_principal', false);
                    })
                    ->with(['assure.user.personne', 'question'])
                    ->get();
    }


    /**
     * Get the principal assure for this demande
     */
    public function assurePrincipal()
    {
        return $this->hasOne(Assure::class, 'user_id', 'user_id')
            ->where('est_principal', true);
    }

    /**
     * Get all beneficiaries for this demande (via the principal assure)
     * Cette méthode retourne une collection, pas une relation Eloquent
     */
    public function getBeneficiairesAttribute()
    {
        // Retourne les bénéficiaires via l'assuré principal
        $assurePrincipal = $this->assurePrincipal;

        if (!$assurePrincipal) {
            return collect([]);
        }

        return $assurePrincipal->beneficiaires;
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
