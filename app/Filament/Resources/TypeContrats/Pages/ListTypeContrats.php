<?php

namespace App\Filament\Resources\TypeContrats\Pages;

use App\Filament\Resources\TypeContrats\TypeContratResource;
use Filament\Resources\Pages\ListRecords;

class ListTypeContrats extends ListRecords
{
    protected static string $resource = TypeContratResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}

