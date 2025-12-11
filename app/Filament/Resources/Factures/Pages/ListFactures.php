<?php

namespace App\Filament\Resources\Factures\Pages;

use App\Filament\Resources\Factures\FactureResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFactures extends ListRecords
{
    protected static string $resource = FactureResource::class;

    protected function getHeaderActions(): array
    {
        // Pas de création de facture - les factures sont créées par les prestataires via l'API
        return [];
    }
}
