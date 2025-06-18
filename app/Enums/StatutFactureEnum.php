<?php

namespace App\Enums;

enum StatutFactureEnum: String
{
    case EN_ATTENTE = 'En attente';
    case VALIDE_MEDECIN = 'Valide médecin';
    case VALIDE_FINANCIER = 'Valide financier';
    case VALIDE_TECHNICIEN = 'Valide technicien';
    case AUTORISER_PAIEMENT = 'Autoriser paiement';
    case REJETE = 'Rejeté';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
