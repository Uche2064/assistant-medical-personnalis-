<?php

namespace App\Enums;

enum LienEnum: String
{
    case PRINCIPAL = 'principal';
    case CONJOINT = 'conjoint';
    case ENFANT = 'enfant';
    case PARENT = 'parent';
    case AUTRE = 'autre';  


    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
