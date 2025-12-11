<?php

namespace App\Filament\Resources\Personnels\Pages;

use App\Filament\Resources\Personnels\PersonnelResource;
use Filament\Resources\Pages\ViewRecord;

class ViewPersonnel extends ViewRecord
{
    protected static string $resource = PersonnelResource::class;

    protected function getHeaderActions(): array
    {
        // Pas de bouton modifier - personne ne peut modifier un personnel
        return [];
    }
}
