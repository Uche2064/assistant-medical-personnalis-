<?php

namespace App\Enums;

enum ClientTypeEnum: string
{
    case MORAL = 'moral';
    case PHYSIQUE = 'physique';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

}
