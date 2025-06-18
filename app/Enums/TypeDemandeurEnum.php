<?php

namespace App\Enums;

enum TypeDemandeurEnum: String
{
    case PROSPECT = 'Prospect';
    case CENTRE_DE_SOINS = 'Centre de soins';
    case MEDECIN_LIBERAL = "Médecin libéral";
    case PHARMACIE = 'Pharmacie';
    case LABORATOIRE = 'Laboratoire';
    case OPTIQUE = 'Optique';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
