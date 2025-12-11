<?php

namespace App\Filament\Widgets;

use App\Enums\StatutDemandeAdhesionEnum;
use App\Enums\TypeDemandeurEnum;
use App\Models\DemandeAdhesion;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class MedecinControleurDemandesPrestatairesWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $user = Auth::guard('web')->user();
        
        if (!$user) {
            return [
                Stat::make('Total Demandes', 0)
                    ->description('Aucune demande')
                    ->color('gray'),
            ];
        }

        // Filtrer uniquement les demandes de type prestataire
        $baseQuery = DemandeAdhesion::where('type_demandeur', TypeDemandeurEnum::PRESTATAIRE->value);

        $total = $baseQuery->count();
        $enAttente = (clone $baseQuery)->where('statut', StatutDemandeAdhesionEnum::EN_ATTENTE->value)->count();
        $validees = (clone $baseQuery)->where('statut', StatutDemandeAdhesionEnum::VALIDEE->value)->count();
        $rejetees = (clone $baseQuery)->where('statut', StatutDemandeAdhesionEnum::REJETEE->value)->count();

        return [
            Stat::make('Total Demandes', $total)
                ->description('Nombre total de demandes prestataires')
                ->descriptionIcon('heroicon-o-document-text')
                ->color('primary'),

            Stat::make('En Attente', $enAttente)
                ->description('Demandes en attente de traitement')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('Validées', $validees)
                ->description('Demandes validées')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Rejetées', $rejetees)
                ->description('Demandes rejetées')
                ->descriptionIcon('heroicon-o-x-circle')
                ->color('danger'),
        ];
    }
}

