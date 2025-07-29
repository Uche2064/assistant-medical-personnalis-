<?php

namespace App\Enums;

enum TypeDemandeurEnum: string
{
    case PHYSIQUE = 'physique';
    case CENTRE_DE_SOINS = 'centre_de_soins';
    case LABORATOIRE_CENTRE_DIAGNOSTIC = 'laboratoire_centre_diagnostic';
    case PHARMACIE = 'pharmacie';
    case OPTIQUE = 'optique';
    case AUTRE = 'autre';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function getLabel(): string
    {
        return match($this) {
            self::PHYSIQUE => 'Client Physique',
            self::CENTRE_DE_SOINS => 'Centre de Soins',
            self::LABORATOIRE_CENTRE_DIAGNOSTIC => 'Laboratoire/Centre de Diagnostic',
            self::PHARMACIE => 'Pharmacie',
            self::OPTIQUE => 'Optique',
            self::AUTRE => 'Autre',
        };
    }
} 