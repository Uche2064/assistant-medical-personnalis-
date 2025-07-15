<?php

namespace App\Enums;

enum TypeContratEnum: String
{
    case BASCI = "basic";
    case STANDARD = "standard";
    case PREMIUM = "premium";
    case TEAM = "team";

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
    
}
