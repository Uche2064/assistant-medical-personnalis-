<?php

namespace App\Enums;

enum TypeClientEnum: String
{
    case PHYSIQUE = 'physique';
    case ENTREPRISE = 'entreprise';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
