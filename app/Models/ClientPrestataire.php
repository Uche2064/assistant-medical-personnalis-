<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientPrestataire extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_contrat_id',
        'prestataire_id',
        'type_prestataire',
        'statut',
    ];

    /**
     * Relation avec le contrat client
     */
    public function clientContrat(): BelongsTo
    {
        return $this->belongsTo(ClientContrat::class, 'client_contrat_id');
    }

    /**
     * Relation avec le prestataire
     */
    public function prestataire(): BelongsTo
    {
        return $this->belongsTo(Prestataire::class, 'prestataire_id');
    }

    /**
     * Vérifier si l'assignation est active
     */
    public function isActif(): bool
    {
        return $this->statut === 'ACTIF';
    }

    /**
     * Obtenir le libellé du type de prestataire
     */
    public function getTypePrestataireLabel(): string
    {
        return match($this->type_prestataire) {
            'pharmacie' => 'Pharmacie',
            'centre_soins' => 'Centre de Soins',
            'optique' => 'Optique',
            'laboratoire_centre_diagnostic' => 'Laboratoire et centre de diagnostic',
            default => ucfirst($this->type_prestataire),
        };
    }
}
