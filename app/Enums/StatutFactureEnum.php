<?php

namespace App\Enums;

enum StatutFactureEnum: String
{
    case EN_ATTENTE = 'en_attente';
    case VALIDE_MEDECIN = 'valide_medecin';
    case VALIDE_FINANCIER = 'valide_financier';
    case VALIDE_TECHNICIEN = 'valide_technicien';
    case AUTORISER_PAIEMENT = 'autoriser_paiement';
    case REJETE = 'rejeté';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
