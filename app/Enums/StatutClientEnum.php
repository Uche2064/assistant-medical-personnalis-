<?php

namespace App\Enums;

enum StatutClientEnum: string
{
    case PROSPECT = 'prospect';
    case CLIENT = 'client';
    case ASSURE = 'assure';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function getLabel(): string
    {
        return match($this) {
            self::PROSPECT => 'Prospect',
            self::CLIENT => 'Client',
            self::ASSURE => 'Assuré',
        };
    }

    public function getColor(): string
    {
        return match($this) {
            self::PROSPECT => 'warning',
            self::CLIENT => 'info',
            self::ASSURE => 'success',
        };
    }
} 