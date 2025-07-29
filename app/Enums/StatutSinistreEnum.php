<?php

namespace App\Enums;

enum StatutSinistreEnum: string
{
    case DECLARE = 'declare';
    case EN_COURS = 'en_cours';
    case TRAITE = 'traite';
    case CLOTURE = 'cloture';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function getLabel(): string
    {
        return match($this) {
            self::DECLARE => 'Déclaré',
            self::EN_COURS => 'En Cours',
            self::TRAITE => 'Traité',
            self::CLOTURE => 'Clôturé',
        };
    }

    public function getColor(): string
    {
        return match($this) {
            self::DECLARE => 'warning',
            self::EN_COURS => 'info',
            self::TRAITE => 'success',
            self::CLOTURE => 'grey',
        };
    }
} 