<?php

namespace App\Filament\Resources\TypeContrats\Pages;

use App\Filament\Resources\TypeContrats\TypeContratResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateTypeContrat extends CreateRecord
{
    protected static string $resource = TypeContratResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Si c'est un technicien qui crée, définir automatiquement le technicien_id
        $user = Auth::user();
        if ($user && $user->hasRole(\App\Enums\RoleEnum::TECHNICIEN->value) && $user->personnel) {
            $data['technicien_id'] = $user->personnel->id;
        }

        return $data;
    }
}

