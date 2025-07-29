<?php

namespace App\Enums;

enum StatutAssureEnum: string
{
    case ACTIF = 'actif';
    case INACTIF = 'inactif';
    case SUSPENDU = 'suspendu';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function getLabel(): string
    {
        return match($this) {
            self::ACTIF => 'Actif',
            self::INACTIF => 'Inactif',
            self::SUSPENDU => 'Suspendu',
        };
    }

    public function getColor(): string
    {
        return match($this) {
            self::ACTIF => 'success',
            self::INACTIF => 'grey',
            self::SUSPENDU => 'warning',
        };
    }
} 