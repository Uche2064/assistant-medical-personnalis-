<?php

namespace App\Enums;

enum TypePersonnelEnum: string
{
    case TECHNICIEN = 'Technicien';
    case COMMERCIAL = 'Commercial';
    case MEDECIN_CONTROLEUR = 'Médecin Contrôleur';
    case COMPTABLE = 'Comptable';

    public static function values() : array
    {
        return array_column(self::cases(), 'value');
    }
}