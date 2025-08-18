<?php

namespace App\Enums;

enum StatutSinistreEnum: string
{
    case EN_COURS = 'en_cours';
    case CLOTURE = 'cloture';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function getLabel(): string
    {
        return match($this) {
            self::EN_COURS => 'En Cours',
            self::CLOTURE => 'Clôturé',
        };
    }

    public function getColor(): string
    {
        return match($this) {
            self::EN_COURS => 'info',
            self::CLOTURE => 'grey',
        };
    }
} 