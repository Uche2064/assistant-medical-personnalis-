<?php

namespace App\Enums;

enum SexeEnum: string
{
    case MASCULIN = 'M';
    case FEMININ = 'F';

    public static function values() : array
    {
        return array_column(self::cases(), 'value');
    }

    public function getLabel(): string
    {
        return match($this) {
            self::MASCULIN => 'Masculin',
            self::FEMININ => 'Féminin',
        };
    }

    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->getLabel();
        }
        return $options;
    }
}
