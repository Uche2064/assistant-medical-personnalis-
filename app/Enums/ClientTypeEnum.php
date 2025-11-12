<?php

namespace App\Enums;

enum ClientTypeEnum: string
{
    case MORAL = 'moral';
    case PHYSIQUE = 'physique';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function getLabel(): string
    {
        return match($this) {
            self::MORAL => 'Entreprise',
            self::PHYSIQUE => 'Particulier',
        };
    }

}
