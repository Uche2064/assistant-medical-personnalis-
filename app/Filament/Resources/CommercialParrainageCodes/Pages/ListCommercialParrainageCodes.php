<?php

namespace App\Filament\Resources\CommercialParrainageCodes\Pages;

use App\Filament\Resources\CommercialParrainageCodes\CommercialParrainageCodeResource;
use Filament\Resources\Pages\ListRecords;

class ListCommercialParrainageCodes extends ListRecords
{
    protected static string $resource = CommercialParrainageCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Pas de bouton de création car les codes sont générés via l'API
        ];
    }
}

