<?php

namespace App\Enums;

enum TypeClientEnum: String
{
    case CLIENT_PHYSIQUE = 'client_physique';
    case CLIENT_MORAL = 'client_moral';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
