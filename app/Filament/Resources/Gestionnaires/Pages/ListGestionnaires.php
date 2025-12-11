<?php

namespace App\Filament\Resources\Gestionnaires\Pages;

use App\Enums\RoleEnum;
use App\Filament\Resources\Gestionnaires\GestionnaireResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListGestionnaires extends ListRecords
{
    protected static string $resource = GestionnaireResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
    
    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            ->whereHas('user.roles', function ($q) {
                $q->where('name', RoleEnum::GESTIONNAIRE->value);
            });
    }
}

