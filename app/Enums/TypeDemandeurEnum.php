<?php

namespace App\Enums;

enum TypeDemandeurEnum: string
{
    case CLIENT = 'client';
    case PRESTATAIRE = 'prestataire';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function getLabelKey(string $typeDemandeur): string
    {
        return match($typeDemandeur) {
            self::CLIENT->value => 'client',
            self::PRESTATAIRE->value => 'prestataire',
        };
    }

    public function getLabel(): string
    {
        return match($this) {
            self::CLIENT => 'Client',
            self::PRESTATAIRE => 'Prestataire',
        };
    }
} 