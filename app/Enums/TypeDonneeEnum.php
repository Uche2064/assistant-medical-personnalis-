<?php

namespace App\Enums;

enum TypeDonneeEnum: String
{
    case TEXT = 'text';
    case NUMBER = 'number';
    case BOOLEAN = 'boolean';
    case DATE = 'date';
    case SELECT = 'select';
    case CHECKBOX = 'checkbox';
    case RADIO = 'radio';

    public static function values() : array {
        return array_column(self::cases(), 'value');
    }
}
