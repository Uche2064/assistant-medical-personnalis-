<?php

namespace App\Enums;

enum DestinataireQuestionEnum: String
{
    case PROSPECT = 'prospect';
    case PRESTATAIRE = 'prestataire';

    public static function values(): array 
    {
        return array_column(self::cases(), 'value');
    }
}
