<?php

namespace App\Enums;

enum TypeDemandeurEnum: string
{
    case CLIENT = 'client';
    case CENTRE_DE_SOINS = 'centre_de_soins';
    case LABORATOIRE_CENTRE_DIAGNOSTIC = 'laboratoire_centre_diagnostic';
    case PHARMACIE = 'pharmacie';
    case OPTIQUE = 'optique';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function getLabelKey(string $typeDemandeur): string
    {
        return match($typeDemandeur) {
            self::CLIENT->value => 'client',
            self::CENTRE_DE_SOINS->value => 'centre_de_soins',
            self::LABORATOIRE_CENTRE_DIAGNOSTIC->value => 'laboratoire_centre_diagnostic',
            self::PHARMACIE->value => 'pharmacie',
            self::OPTIQUE->value => 'optique',
        };
    }

    public function getLabel(): string
    {
        return match($this) {
            self::CLIENT => 'Client Physique',
            self::CENTRE_DE_SOINS => 'Centre de Soins',
            self::LABORATOIRE_CENTRE_DIAGNOSTIC => 'Laboratoire/Centre de Diagnostic',
            self::PHARMACIE => 'Pharmacie',
            self::OPTIQUE => 'Optique',
        };
    }

    public static function getPrestataire(): array
    {
        return [
            self::CENTRE_DE_SOINS->value,
            self::LABORATOIRE_CENTRE_DIAGNOSTIC->value,
            self::PHARMACIE->value,
            self::OPTIQUE->value,
        ];
    }
} 