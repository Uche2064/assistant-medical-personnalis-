<?php

namespace App\Filament\Resources\Clients\Pages;

use App\Enums\RoleEnum;
use App\Filament\Resources\Clients\ClientResource;
use Filament\Actions\CreateAction;
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
        if (Auth::check() && Auth::user()->hasRole(RoleEnum::COMMERCIAL->value)) {
            $commercial = Auth::user();
            $query->whereHas('user', function ($q) use ($commercial) {
                $q->where('personne_id', $commercial->id)
                  ->whereHas('roles', fn($roleQuery) => 
                      $roleQuery->where('name', RoleEnum::CLIENT->value)
                  );
            });
        }
        // Si admin_global, voir tous les clients
        
        return $query;
    }
}
