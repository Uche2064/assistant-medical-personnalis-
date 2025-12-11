<?php

namespace App\Filament\Resources\CategorieGaranties\Pages;

use App\Filament\Resources\CategorieGaranties\CategorieGarantieResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCategorieGarantie extends ViewRecord
{
    protected static string $resource = CategorieGarantieResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->color('info'),
            DeleteAction::make(),
        ];
    }
}

