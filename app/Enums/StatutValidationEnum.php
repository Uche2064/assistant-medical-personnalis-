<?php

namespace App\Enums;

enum StatutValidationEnum: String
{
    case EN_ATTENTE = 'en_attente';
    case VALIDEE = 'validée';
    case REJETEE = 'rejetée';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
