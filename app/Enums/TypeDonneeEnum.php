<?php

namespace App\Enums;

enum TypeDonneeEnum: string
{
    case TEXT = 'text';
    case NUMBER = 'number';
    case BOOLEAN = 'boolean';
    case DATE = 'date';
    case SELECT = 'select';
    case CHECKBOX = 'checkbox';
    case RADIO = 'radio';
    case FILE = 'file';

    public static function values() : array {
        return array_column(self::cases(), 'value');
    }

    public static function getLabelKey(string $typeDonnee): string
    {
        return match($typeDonnee) {
            self::TEXT->value => 'text',
            self::NUMBER->value => 'number',
            self::BOOLEAN->value => 'boolean',
            self::DATE->value => 'date',
            self::SELECT->value => 'select',
            self::CHECKBOX->value => 'checkbox',
            self::RADIO->value => 'radio',
            self::FILE->value => 'file',
        };
    }

    public function getLabel(): string
    {
        return match($this) {
            self::TEXT => 'Texte',
            self::NUMBER => 'Nombre',
            self::BOOLEAN => 'Oui/Non',
            self::DATE => 'Date',
            self::SELECT => 'Sélection',
            self::CHECKBOX => 'Case à cocher',
            self::RADIO => 'Bouton radio',
            self::FILE => 'Fichier',
        };
    }
}
