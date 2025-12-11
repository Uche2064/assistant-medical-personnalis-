<?php

namespace App\Filament\Resources\CategorieGaranties\Pages;

use App\Filament\Resources\CategorieGaranties\CategorieGarantieResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateCategorieGarantie extends CreateRecord
{
    protected static string $resource = CategorieGarantieResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Définir automatiquement le medecin_controleur_id avec l'ID du personnel de l'utilisateur connecté
        // La migration référence personnels.id, donc on utilise personnel->id
        $user = Auth::user();
        if ($user && $user->personnel) {
            $data['medecin_controleur_id'] = $user->personnel->id;
        }

        return $data;
    }
}

