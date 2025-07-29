<?php

namespace App\Enums;

enum RoleEnum: String
{
    case ADMIN_GLOBAL = "admin_global";
    case TECHNICIEN = 'technicien';
    case COMMERCIAL = 'commercial';
    case MEDECIN_CONTROLEUR = 'medecin_controleur';
    case COMPTABLE = 'comptable';
    case GESTIONNAIRE = "gestionnaire";
    case USER = 'user';

    public static function values(): array {
        return array_column(self::cases(), 'value');
    }
}
