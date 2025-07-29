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