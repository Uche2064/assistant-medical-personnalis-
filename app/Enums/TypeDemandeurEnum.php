<?php

namespace App\Enums;

enum TypeDemandeurEnum: String
{
    case PROSPECT_PHYSIQUE = 'prospect_physique';
    case PROSPECT_MORAL = 'prospect_moral';
    case CENTRE_DE_SOINS = 'centre_de_soins';
    case MEDECIN_LIBERAL = "medecin_liberal";
    case PHARMACIE = 'pharmacie';
    case LABORATOIRE = 'laboratoire';
    case OPTIQUE = 'optique';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
