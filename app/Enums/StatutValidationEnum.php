<?php

namespace App\Enums;

enum StatutValidationEnum: String
{
    case EN_ATTENTE = 'En attente';
    case VALIDE = 'Validé';
    case REJETE = 'Rejeté';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
