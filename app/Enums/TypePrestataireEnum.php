<?php

namespace App\Enums;

enum TypePrestataireEnum: String
{
    case CENTE_DE_SOIN = "Centre de soins";
    case PHARMACIE = "Pharmacie";
    case OPTIQUE = "Optique";
    case LABORATOIRE = "Laboratoire";
    case PARTICULIER = "Particulier";

    public static function values() : array
    {
        return array_column(self::cases(), 'value');
    }
}

