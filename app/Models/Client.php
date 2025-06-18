<?php

namespace App\Models;

use App\Enums\TypeClientEnum;
use App\Enums\StatutValidationEnum;
use App\Enums\LienParenteEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'utilisateur_id',
        'gestionnaire_id',
        'client_principal_id',
        'profession',
        'type_client',
        'statut_validation',
        'prime',
        'date_paiement_prime',
        'est_assure',
        'est_principal',
        'lien_parente',
    ];

    protected function casts(): array
    {
        return [
            'type_client' => TypeClientEnum::class,
            'statut_validation' => StatutValidationEnum::class,
            'prime' => 'decimal:2',
            'date_paiement_prime' => 'date',
            'est_assure' => 'boolean',
            'est_principal' => 'boolean',
            'lien_parente' => LienParenteEnum::class,
        ];
    }

    // Relation vers l'utilisateur
    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'utilisateur_id');
    }

    // Relation vers le gestionnaire
    public function gestionnaire()
    {
        return $this->belongsTo(Gestionnaire::class);
    }

    // Relation vers le client principal (si bénéficiaire)
    public function principal()
    {
        return $this->belongsTo(Client::class, 'client_principal_id');
    }

    // Tous les bénéficiaires rattachés à ce client (si principal)
    public function beneficiaires()
    {
        return $this->hasMany(Client::class, 'client_principal_id');
    }

    // Est-ce le titulaire du contrat ?
    public function isPrincipal(): bool
    {
        return $this->est_principal;
    }

    // Est-ce un bénéficiaire ?
    public function isBeneficiaire(): bool
    {
        return !$this->est_principal && $this->client_principal_id !== null;
    }

    // Est-il assuré (contrat actif) ?
    public function isAssure(): bool
    {
        return $this->est_assure;
    }

    // Scope pour récupérer tous les principaux
    public function scopePrincipaux($query)
    {
        return $query->where('est_principal', true);
    }

    // Scope pour récupérer tous les bénéficiaires
    public function scopeBeneficiaires($query)
    {
        return $query->where('est_principal', false)->whereNotNull('client_principal_id');
    }
}