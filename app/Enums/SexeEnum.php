<?php

namespace App\Enums;

enum SexeEnum: string
{
    case MASCULIN = 'M';
    case FEMININ = 'F';

    public static function values() : array
    {
        return array_column(self::cases(), 'value');
    }
}
