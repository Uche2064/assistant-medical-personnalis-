<?php

namespace App\Models;

use App\Enums\StatutContratEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;


class ClientContrat extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'contrat_id',
        'type_client',
        'date_debut',
        'date_fin',
        'statut',
        'numero_police'
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'statut' => StatutContratEnum::class
    ];


    public function genererNumeroPolice() {
        return Str::uuid()->toString();
    }

    /**
     * Relation avec l'utilisateur client
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Relation avec le contrat
     */
    public function contrat(): BelongsTo
    {
        return $this->belongsTo(Contrat::class, 'contrat_id');
    }

    /**
     * Relation avec les prestataires assignés
     */
    public function prestataires(): HasMany
    {
        return $this->hasMany(ClientPrestataire::class, 'client_contrat_id');
    }

    /**
     * Vérifier si le contrat est actif
     */
    public function isActif(): bool
    {
        return $this->statut === 'ACTIF' && $this->date_fin >= now();
    }

    /**
     * Vérifier si le contrat est expiré
     */
    public function isExpire(): bool
    {
        return $this->date_fin < now();
    }
}
