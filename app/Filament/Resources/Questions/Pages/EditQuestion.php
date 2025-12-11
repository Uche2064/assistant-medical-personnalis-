<?php

namespace App\Filament\Resources\Questions\Pages;

use App\Filament\Resources\Questions\QuestionResource;
use Filament\Resources\Pages\EditRecord;

class EditQuestion extends EditRecord
{
    protected static string $resource = QuestionResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Transformer les options du Repeater en tableau simple pour le JSON
        if (isset($data['options']) && is_array($data['options'])) {
            $data['options'] = array_map(function ($item) {
                return is_array($item) ? ($item['option'] ?? $item) : $item;
            }, $data['options']);
            // Si le tableau est vide, mettre null
            if (empty($data['options'])) {
                $data['options'] = null;
            }
        }

        return $data;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Transformer le JSON des options en format Repeater (array d'objets avec 'option')
        if (isset($data['options']) && is_array($data['options'])) {
            $data['options'] = array_map(function ($option) {
                return ['option' => $option];
            }, $data['options']);
        }

        return $data;
    }
}

