<?php

namespace App\Enums;

enum LienEnum: String
{
    case PRINCIPAL = 'Principal';
    case CONJOINT = 'Conjoint';
    case ENFANT = 'Enfant';
    case PARENT = 'Parent';
    case AUTRE = 'Autre';  
    case SOCIETE = 'societe';


    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
