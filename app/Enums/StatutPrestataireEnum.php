<?php

namespace App\Enums;

enum StatutPrestataireEnum: string
{
    case EN_ATTENTE = 'en_attente';
    case VALIDE = 'valide';
    case REJETE = 'rejete';
    case SUSPENDU = 'suspendu';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function getLabel(): string
    {
        return match($this) {
            self::EN_ATTENTE => 'En Attente',
            self::VALIDE => 'Validé',
            self::REJETE => 'Rejeté',
            self::SUSPENDU => 'Suspendu',
        };
    }

    public function getColor(): string
    {
        return match($this) {
            self::EN_ATTENTE => 'warning',
            self::VALIDE => 'success',
            self::REJETE => 'error',
            self::SUSPENDU => 'grey',
        };
    }
} 