<?php

namespace App\Enums;

enum TypeContratEnum: string
{
    case BASIC = 'basic';
    case STANDARD = 'standard';
    case PREMIUM = 'premium';
    case TEAM = 'team';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function getLabel(): string
    {
        return match($this) {
            self::BASIC => 'Basique',
            self::STANDARD => 'Standard',
            self::PREMIUM => 'Premium',
            self::TEAM => 'Équipe',
        };
    }

    public function getDescription(): string
    {
        return match($this) {
            self::BASIC => 'Contrat de base avec couverture minimale',
            self::STANDARD => 'Contrat standard avec couverture complète',
            self::PREMIUM => 'Contrat premium avec couverture étendue',
            self::TEAM => 'Contrat pour équipes/entreprises',
        };
    }
}
