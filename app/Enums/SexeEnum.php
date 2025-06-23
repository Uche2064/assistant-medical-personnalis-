<?php

namespace App\Enums;

enum SexeEnum: string
{
    case MASCULIN = 'm';
    case FEMININ = 'f';

    public static function values() : array
    {
        return array_column(self::cases(), 'value');
    }
}
