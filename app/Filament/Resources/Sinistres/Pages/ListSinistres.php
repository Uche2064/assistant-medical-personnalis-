<?php

namespace App\Filament\Resources\Sinistres\Pages;

use App\Filament\Resources\Sinistres\SinistreResource;
use Filament\Resources\Pages\ListRecords;

class ListSinistres extends ListRecords
{
    protected static string $resource = SinistreResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}

