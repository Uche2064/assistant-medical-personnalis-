<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use App\Models\PropositionContrat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class TechnicienClientsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $user = Auth::user();
        
        if (!$user || !$user->personnel) {
            return [
                Stat::make('Total Clients', 0)
                    ->description('Aucun client')
                    ->color('gray'),
                Stat::make('Clients Actifs', 0)
                    ->description('Aucun client actif')
                    ->color('gray'),
                Stat::make('Clients Inactifs', 0)
                    ->description('Aucun client inactif')
                    ->color('gray'),
            ];
        }

        $technicien = $user->personnel;

        // Récupérer les clients via les propositions de contrats créées par ce technicien
        $clientIds = PropositionContrat::where('technicien_id', $technicien->id)
            ->whereHas('demandeAdhesion', function ($query) {
                $query->whereHas('user', function ($userQuery) {
                    $userQuery->whereHas('client');
                });
            })
            ->get()
            ->map(function ($proposition) {
                return $proposition->demandeAdhesion->user->client->id ?? null;
            })
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $totalClients = count($clientIds);
        
        // Clients actifs : ceux qui ont au moins un contrat actif
        $clientsActifs = Client::whereIn('id', $clientIds)
            ->whereHas('clientsContrats', function ($query) {
                $query->where('statut', 'actif')
                      ->where('date_fin', '>=', now());
            })
            ->count();

        // Clients inactifs : ceux qui n'ont pas de contrat actif
        $clientsInactifs = $totalClients - $clientsActifs;

        return [
            Stat::make('Total Clients', $totalClients)
                ->description('Nombre total de clients')
                ->descriptionIcon('heroicon-o-users')
                ->color('primary'),

            Stat::make('Clients Actifs', $clientsActifs)
                ->description('Clients avec contrat actif')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Clients Inactifs', $clientsInactifs)
                ->description('Clients sans contrat actif')
                ->descriptionIcon('heroicon-o-x-circle')
                ->color('danger'),
        ];
    }
}

