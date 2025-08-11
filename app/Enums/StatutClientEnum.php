<?php

namespace App\Enums;

enum StatutClientEnum: string
{
    case BENEFICIAIRE = 'beneficiaire';
    case ASSURE = 'assure';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

} 