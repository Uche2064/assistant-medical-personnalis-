<?php

namespace App\Filament\Resources\Questions\Pages;

use App\Filament\Resources\Questions\QuestionResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateQuestion extends CreateRecord
{
    protected static string $resource = QuestionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Utiliser Filament::auth() pour obtenir l'utilisateur dans le contexte Filament
        $user = Filament::auth()->user() ?? Auth::guard('web')->user();
        
        if (!$user) {
            throw new \Exception('Aucun utilisateur connecté. Impossible de créer une question.');
        }
        
        // Charger la relation personnel si elle n'est pas déjà chargée
        if (!$user->relationLoaded('personnel')) {
            $user->load('personnel');
        }
        
        if ($user->personnel) {
            $data['cree_par_id'] = $user->personnel->id;
        } else {
            // Si l'utilisateur n'a pas de personnel, lever une exception
            throw new \Exception('L\'utilisateur connecté n\'a pas de personnel associé. Impossible de créer une question.');
        }

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
}

