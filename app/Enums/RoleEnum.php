<?php

namespace App\Enums;

enum RoleEnum: String
{
    case ADMIN_GLOBAL = "Admin Global";
    case PERSONNEL = "Personnel";
    case GESTIONNAIRE = "Gestionnaire";

    public static function values(): array {
        return array_column(self::cases(), 'value');
    }
}
