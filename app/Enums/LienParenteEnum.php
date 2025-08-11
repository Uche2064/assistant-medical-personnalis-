<?php

namespace App\Enums;

enum LienParenteEnum: string
{
    case CONJOINT = 'conjoint';
    case ENFANT = 'enfant';
    case PARENT = 'parent';
    case AUTRE = 'autre';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function getLabel(): string
    {
        return match($this) {
            self::CONJOINT => 'Conjoint(e)',
            self::ENFANT => 'Enfant',
            self::PARENT => 'Parent',
            self::AUTRE => 'Autre',
        };
    }
} 