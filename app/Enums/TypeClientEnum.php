<?php

namespace App\Enums;

enum TypeClientEnum: String
{
    case PHYSIQUE = 'physique';
    case MORAL = 'moral';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
