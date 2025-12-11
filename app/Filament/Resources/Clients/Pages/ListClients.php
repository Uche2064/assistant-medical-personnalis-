<?php

namespace App\Filament\Resources\Clients\Pages;

use App\Enums\RoleEnum;
use App\Filament\Resources\Clients\ClientResource;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListClients extends ListRecords
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        // Si l'utilisateur est un commercial, filtrer seulement ses clients
        $user = Filament::auth()->user() ?? Auth::user();

        if ($user && $user->hasRole(RoleEnum::COMMERCIAL->value)) {
            // Filtrer directement par commercial_id dans la table clients
            $query->where('commercial_id', $user->id);
        }
        // Si admin_global ou technicien, voir tous les clients (pas de filtre)

        return $query;
    }
}
