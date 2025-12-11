<?php

namespace App\Filament\Resources\CategorieGaranties\Pages;

use App\Filament\Resources\CategorieGaranties\CategorieGarantieResource;
use Filament\Resources\Pages\ListRecords;

class ListCategorieGaranties extends ListRecords
{
    protected static string $resource = CategorieGarantieResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}

