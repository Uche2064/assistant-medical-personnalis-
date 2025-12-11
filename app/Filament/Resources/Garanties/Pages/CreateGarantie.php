<?php

namespace App\Filament\Resources\Garanties\Pages;

use App\Filament\Resources\Garanties\GarantieResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateGarantie extends CreateRecord
{
    protected static string $resource = GarantieResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Définir automatiquement le medecin_controleur_id avec l'ID du personnel de l'utilisateur connecté
        // Comme dans l'API : Auth::user()->personnel->id
        $user = Auth::user();
        if ($user && $user->personnel) {
            $data['medecin_controleur_id'] = $user->personnel->id;
        }

        return $data;
    }
}

