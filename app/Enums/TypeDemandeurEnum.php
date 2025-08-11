<?php

namespace App\Enums;

enum TypeDemandeurEnum: string
{
    case PHYSIQUE = 'physique';
    case CENTRE_DE_SOINS = 'centre_de_soins';
    case LABORATOIRE_CENTRE_DIAGNOSTIC = 'laboratoire_centre_diagnostic';
    case PHARMACIE = 'pharmacie';
    case OPTIQUE = 'optique';
    case ENTREPRISE = 'entreprise';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function getLabelKey(string $typeDemandeur): string
    {
        return match($typeDemandeur) {
            self::PHYSIQUE->value => 'physique',
            self::CENTRE_DE_SOINS->value => 'centre_de_soins',
            self::LABORATOIRE_CENTRE_DIAGNOSTIC->value => 'laboratoire_centre_diagnostic',
            self::PHARMACIE->value => 'pharmacie',
            self::OPTIQUE->value => 'optique',
            self::ENTREPRISE->value => 'entreprise',
        };
    }

    public function getLabel(): string
    {
        return match($this) {
            self::PHYSIQUE => 'Client Physique',
            self::CENTRE_DE_SOINS => 'Centre de Soins',
            self::LABORATOIRE_CENTRE_DIAGNOSTIC => 'Laboratoire/Centre de Diagnostic',
            self::PHARMACIE => 'Pharmacie',
            self::OPTIQUE => 'Optique',
            self::ENTREPRISE => 'Entreprise',
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