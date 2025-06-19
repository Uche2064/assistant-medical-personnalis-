<?php

namespace App\Models;

use App\Enums\LienEnum;
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
        'user_id',
        'gestionnaire_id',
        'profession',
        'type_client',
        'statut_validation',
        'prime',
        'date_paiement_prime',
        'lien_parente',
    ];

    protected function casts(): array
    {
        return [
            'type_client' => TypeClientEnum::class,
            'statut_validation' => StatutValidationEnum::class,
            'prime' => 'decimal:2',
            'date_paiement_prime' => 'date',
            'lien_parente' => LienEnum::class,
        ];
    }

    // Relation vers l'utilisateur
    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}