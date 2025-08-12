<?php

namespace App\Enums;

enum StatutClientPrestataireEnum: string
{
    case ACTIF = 'actif';
    case INACTIF = 'inactif';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function getLabel(): string
    {
        return match($this) {
            self::ACTIF => 'Actif',
            self::INACTIF => 'Inactif',
        };
    }
}
