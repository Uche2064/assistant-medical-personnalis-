<?php

namespace App\Enums;

enum TypePrestataireEnum: string
{
    case CENTRE_DE_SOINS = 'centre_de_soins';
    case LABORATOIRE_CENTRE_DIAGNOSTIC = 'laboratoire_centre_diagnostic';
    case MEDECIN_LIBERAL = 'medecin_liberal';
    case PHARMACIE = 'pharmacie';
    case OPTIQUE = 'optique';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function getLabel(): string
    {
        return match($this) {
            self::CENTRE_DE_SOINS => 'Centre de Soins',
            self::LABORATOIRE_CENTRE_DIAGNOSTIC => 'Laboratoire/Centre de Diagnostic',
            self::MEDECIN_LIBERAL => 'Médecin Libéral',
            self::PHARMACIE => 'Pharmacie',
            self::OPTIQUE => 'Optique',
        };
    }
} 