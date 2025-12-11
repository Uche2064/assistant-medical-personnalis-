<?php

namespace App\Filament\Resources\Garanties\Pages;

use App\Filament\Resources\Garanties\GarantieResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewGarantie extends ViewRecord
{
    protected static string $resource = GarantieResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->color('info'),
            DeleteAction::make(),
        ];
    }
}

