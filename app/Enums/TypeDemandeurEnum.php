<?php

namespace App\Enums;

enum TypeDemandeurEnum: String
{
    case PROSPECT_PHYSIQUE = 'physique';
    case PROSPECT_MORAL = 'moral';
    case CENTRE_DE_SOINS = 'centre_de_soins';
    case MEDECIN_LIBERAL = "medecin_liberal";
    case PHARMACIE = 'pharmacie';
    case LABORATOIRE_CENTRE_DIAGNOSTIC = 'laboratoire_centre_diagnostic';
    case OPTIQUE = 'optique';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
