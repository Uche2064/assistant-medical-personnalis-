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

    protected $table = 'clients_contrats';

    protected $fillable = [
        'client_id',
        'type_contrat_id',
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


    /**
     * Generate a unique police number.
     */
    public static function generateNumeroPolice()
    {
        do {
            $numero = 'POL' . date('Y') . strtoupper(substr(md5(uniqid()), 0, 8));
        } while (self::where('numero_police', $numero)->exists());

        return $numero;
    }


    public function genererNumeroPolice() {
        return Str::uuid()->toString();
    }

    /**
     * Relation avec l'utilisateur client
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    /**
     * Relation avec le contrat
     */
    public function typeContrat(): BelongsTo
    {
        return $this->belongsTo(TypeContrat::class, 'type_contrat_id');
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
        return $this->statut === 'actif' && $this->date_fin >= now();
    }

    /**
     * Vérifier si le contrat est expiré
     */
    public function isExpire(): bool
    {
        return $this->date_fin < now();
    }

    public function getLabelStatut(): string
    {
        return $this->statut->getLabel();
    }
}
