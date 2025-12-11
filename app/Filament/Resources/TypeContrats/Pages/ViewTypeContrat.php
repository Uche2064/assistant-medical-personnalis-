<?php

namespace App\Filament\Resources\TypeContrats\Pages;

use App\Filament\Resources\TypeContrats\TypeContratResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTypeContrat extends ViewRecord
{
    protected static string $resource = TypeContratResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->color('info'),
            DeleteAction::make(),
        ];
    }
}

