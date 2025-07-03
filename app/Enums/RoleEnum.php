<?php

namespace App\Enums;

enum RoleEnum: String
{
    case ADMIN_GLOBAL = "admin_global";
    case PERSONNEL = "personnel";
    case GESTIONNAIRE = "gestionnaire";
    case ASSURE = "assuré";

    public static function values(): array {
        return array_column(self::cases(), 'value');
    }
}
