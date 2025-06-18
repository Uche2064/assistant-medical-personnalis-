<?php

namespace App\Enums;

enum TypeClientEnum: String
{
    case PARTICULIER = 'Particulier';
    case ENTREPRISE = 'Entreprise';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
