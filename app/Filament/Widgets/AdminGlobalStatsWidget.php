<?php

namespace App\Filament\Widgets;

use App\Enums\RoleEnum;
use App\Models\Assure;
use App\Models\Client;
use App\Models\Personnel;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminGlobalStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        // Nombre total de personnel
        $totalPersonnel = Personnel::whereHas('user.roles', function ($q) {
            $q->whereIn('name', [
                RoleEnum::GESTIONNAIRE->value,
                RoleEnum::COMMERCIAL->value,
                RoleEnum::MEDECIN_CONTROLEUR->value,
                RoleEnum::TECHNICIEN->value,
                RoleEnum::COMPTABLE->value,
            ]);
        })->count();
        
        // Nombre total de clients
        $totalClients = Client::count();
        
        // Nombre total d'assurés
        $totalAssures = Assure::count();
        
        // Assurés principaux
        $assuresPrincipaux = Assure::where('est_principal', true)->count();
        
        // Clients actifs
        $clientsActifs = Client::whereHas('user', fn($q) => $q->where('est_actif', true))->count();
        
        return [
            Stat::make('Total Personnel', $totalPersonnel)
                ->description('Personnel interne')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
            
            Stat::make('Total Clients', $totalClients)
                ->description($clientsActifs . ' actifs')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('success'),
            
            Stat::make('Total Assurés', $totalAssures)
                ->description($assuresPrincipaux . ' principaux')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),
        ];
    }
}
