<?php

namespace App\Enums;

enum TypePrestataireEnum: String
{
    case CENTE_DE_SOIN = "centre_de_soins";
    case PHARMACIE = "pharmacie";
    case OPTIQUE = "optique";
    case LABORATOIRE_CENTRE_DIAGNOSTIC = "laboratoire_centre_diagnostic";
    case PARTICULIER = "particulier";

    public static function values() : array
    {
        return array_column(self::cases(), 'value');
    }
}

