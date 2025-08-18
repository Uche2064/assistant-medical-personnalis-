<?php

namespace App\Enums;

enum StatutFactureEnum: string
{
    case EN_ATTENTE = 'en_attente';
    case VALIDEE_TECHNICIEN = 'validee_technicien';
    case VALIDEE_MEDECIN = 'validee_medecin';
    case AUTORISEE_COMPTABLE = 'autorisee_comptable';
    case REMBOURSEE = 'remboursee';
    case REJETEE = 'rejetee';
    case REJETEE_TECHNICIEN = 'rejetee_technicien';
    case REJETEE_MEDECIN = 'rejetee_medecin';
    case REJETEE_COMPTABLE = 'rejetee_comptable';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function getLabel(): string
    {
        return match($this) {
            self::EN_ATTENTE => 'En Attente',
            self::VALIDEE_TECHNICIEN => 'Validée par Technicien',
            self::VALIDEE_MEDECIN => 'Validée par Médecin',
            self::AUTORISEE_COMPTABLE => 'Autorisée par Comptable',
            self::REMBOURSEE => 'Remboursée',
            self::REJETEE => 'Rejetée',
            self::REJETEE_TECHNICIEN => 'Rejetée par Technicien',
            self::REJETEE_MEDECIN => 'Rejetée par Médecin',
            self::REJETEE_COMPTABLE => 'Rejetée par Comptable',
        };
    }

    public function getColor(): string
    {
        return match($this) {
            self::EN_ATTENTE => 'warning',
            self::VALIDEE_TECHNICIEN => 'info',
            self::VALIDEE_MEDECIN => 'primary',
            self::AUTORISEE_COMPTABLE => 'success',
            self::REMBOURSEE => 'success',
            self::REJETEE => 'error',
            self::REJETEE_TECHNICIEN => 'error',
            self::REJETEE_MEDECIN => 'error',
            self::REJETEE_COMPTABLE => 'error',
        };
    }

    public function getStep(): int
    {
        return match($this) {
            self::EN_ATTENTE => 1,
            self::VALIDEE_TECHNICIEN => 2,
            self::VALIDEE_MEDECIN => 3,
            self::AUTORISEE_COMPTABLE => 4,
            self::REMBOURSEE => 5,
            self::REJETEE => 0,
            self::REJETEE_TECHNICIEN => 0,
            self::REJETEE_MEDECIN => 0,
            self::REJETEE_COMPTABLE => 0,
        };
    }
}
