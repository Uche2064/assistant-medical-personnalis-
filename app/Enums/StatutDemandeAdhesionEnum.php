<?php

namespace App\Enums;

enum StatutDemandeAdhesionEnum: string
{
    case EN_ATTENTE = 'en_attente';
    case PROPOSEE = 'proposee';
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
            self::PROPOSEE => 'Proposée',
            self::ACCEPTEE => 'Acceptée',
            self::VALIDEE => 'Validée',
            self::REJETEE => 'Rejetée',
        };
    }

    public function getColor(): string
    {
        return match($this) {
            self::EN_ATTENTE => 'warning',
            self::PROPOSEE => 'info',
            self::ACCEPTEE => 'success',
            self::VALIDEE => 'success',
            self::REJETEE => 'error',
        };
    }
} 