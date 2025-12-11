<?php

namespace App\Filament\Resources\DemandeAdhesions\Pages;

use App\Filament\Resources\DemandeAdhesions\DemandeAdhesionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditDemandeAdhesion extends EditRecord
{
    protected static string $resource = DemandeAdhesionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
