<?php

namespace App\Filament\Resources\Assures\Pages;

use App\Filament\Resources\Assures\AssureResource;
use Filament\Resources\Pages\ViewRecord;

class ViewAssure extends ViewRecord
{
    protected static string $resource = AssureResource::class;

    public function mount(int | string $record): void
    {
        parent::mount($record);

        // Charger les relations nécessaires
        $this->record->load([
            'user.personne',
            'client.user.personne',
            'assurePrincipal.user.personne',
            'assurePrincipal.user.contact',
            'beneficiaires.user.personne',
            'beneficiaires.user.contact',
        ]);
    }
}

