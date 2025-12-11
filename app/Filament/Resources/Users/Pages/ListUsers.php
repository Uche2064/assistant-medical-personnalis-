<?php

namespace App\Filament\Resources\Users\Pages;

use App\Enums\RoleEnum;
use App\Filament\Resources\Users\UserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
    
    protected function getTableQuery(): Builder
    {
        // Filtrer pour montrer seulement le personnel (rôles internes)
        return parent::getTableQuery()
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', [
                    RoleEnum::GESTIONNAIRE->value,
                    RoleEnum::COMMERCIAL->value,
                    RoleEnum::MEDECIN_CONTROLEUR->value,
                    RoleEnum::TECHNICIEN->value,
                    RoleEnum::COMPTABLE->value,
                    RoleEnum::ADMIN_GLOBAL->value,
                ]);
            })
            ->whereHas('personnel'); // S'assurer qu'il y a un enregistrement Personnel
    }
}
