<?php

namespace App\Filament\Resources\Garanties\Pages;

use App\Filament\Resources\Garanties\GarantieResource;
use Filament\Resources\Pages\ListRecords;

class ListGaranties extends ListRecords
{
    protected static string $resource = GarantieResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}

