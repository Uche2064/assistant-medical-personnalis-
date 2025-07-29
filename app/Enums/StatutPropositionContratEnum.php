<?php

namespace App\Enums;

enum StatutPropositionContratEnum: string
{
    case PROPOSEE = 'proposee';
    case ACCEPTEE = 'acceptee';
    case REFUSEE = 'refusee';
    case EXPIREE = 'expiree';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function getLabel(): string
    {
        return match($this) {
            self::PROPOSEE => 'Proposée',
            self::ACCEPTEE => 'Acceptée',
            self::REFUSEE => 'Refusée',
            self::EXPIREE => 'Expirée',
        };
    }

    public function getColor(): string
    {
        return match($this) {
            self::PROPOSEE => 'warning',
            self::ACCEPTEE => 'success',
            self::REFUSEE => 'error',
            self::EXPIREE => 'grey',
        };
    }
} 