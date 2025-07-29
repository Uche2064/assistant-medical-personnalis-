<?php

namespace App\Enums;

enum StatutContratEnum: string
{
    case PROPOSE = 'propose';
    case ACCEPTE = 'accepte';
    case REFUSE = 'refuse';
    case ACTIF = 'actif';
    case EXPIRE = 'expire';
    case RESILIE = 'resilie';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function getLabel(): string
    {
        return match($this) {
            self::PROPOSE => 'Proposé',
            self::ACCEPTE => 'Accepté',
            self::REFUSE => 'Refusé',
            self::ACTIF => 'Actif',
            self::EXPIRE => 'Expiré',
            self::RESILIE => 'Résilié',
        };
    }

    public function getColor(): string
    {
        return match($this) {
            self::PROPOSE => 'warning',
            self::ACCEPTE => 'success',
            self::REFUSE => 'error',
            self::ACTIF => 'primary',
            self::EXPIRE => 'grey',
            self::RESILIE => 'error',
        };
    }
} 