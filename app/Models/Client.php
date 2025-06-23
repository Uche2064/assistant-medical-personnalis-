<?php

namespace App\Models;

use App\Enums\TypeClientEnum;
use App\Enums\StatutValidationEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'profession',
        'type_client',
        'statut_validation',
        'prime',
        'date_paiement_prime',
    ];

    protected function casts(): array
    {
        return [
            'type_client' => TypeClientEnum::class,
            'statut_validation' => StatutValidationEnum::class,
            'prime' => 'decimal:2',
            'date_paiement_prime' => 'date',
        ];
    }

    // Relation vers l'user
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}