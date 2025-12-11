<?php

namespace App\Filament\Resources\DemandeAdhesions\Pages;

use App\Filament\Resources\DemandeAdhesions\DemandeAdhesionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewDemandeAdhesion extends ViewRecord
{
    protected static string $resource = DemandeAdhesionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
