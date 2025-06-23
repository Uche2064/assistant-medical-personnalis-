<?php

namespace App\Enums;

enum TypePersonnelEnum: string
{
    case TECHNICIEN = 'technicien';
    case COMMERCIAL = 'commercial';
    case MEDECIN_CONTROLEUR = 'medecin_controleur';
    case COMPTABLE = 'comptable';

    public static function values() : array
    {
        return array_column(self::cases(), 'value');
    }
}