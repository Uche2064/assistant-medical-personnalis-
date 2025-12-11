<?php

namespace App\Filament\Resources\ClientPrestataires\Pages;

use App\Filament\Resources\ClientPrestataires\ClientPrestataireResource;
use Filament\Resources\Pages\ListRecords;

class ListClientPrestataires extends ListRecords
{
    protected static string $resource = ClientPrestataireResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}

