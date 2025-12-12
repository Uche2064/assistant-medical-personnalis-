<?php

namespace App\Filament\Resources\Assures\Pages;

use App\Filament\Resources\Assures\AssureResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAssures extends ListRecords
{
    protected static string $resource = AssureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Pas de création manuelle d'assurés
        ];
    }
}

