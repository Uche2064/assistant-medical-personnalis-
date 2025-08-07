<?php

namespace App\Enums;

enum StatutDemandeAdhesionEnum: string
{
    case EN_ATTENTE = 'en_attente';
    case EN_PROPOSITION = 'en_proposition';
    case ACCEPTEE = 'acceptee';
    case VALIDEE = 'validee';
    case REJETEE = 'rejetee';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function getLabel(): string
    {
        return match($this) {
            self::EN_ATTENTE => 'En Attente',
            self::EN_PROPOSITION => 'En Proposition',
            self::ACCEPTEE => 'Acceptée',
            self::VALIDEE => 'Validée',
            self::REJETEE => 'Rejetée',
        };
    }

    public function getColor(): string
    {
        return match($this) {
            self::EN_ATTENTE => 'warning',
            self::EN_PROPOSITION => 'info',
            self::ACCEPTEE => 'success',
            self::VALIDEE => 'success',
            self::REJETEE => 'error',
        };
    }
} 