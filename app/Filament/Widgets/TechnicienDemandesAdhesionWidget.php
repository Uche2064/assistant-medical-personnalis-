<?php

namespace App\Filament\Widgets;

use App\Enums\StatutDemandeAdhesionEnum;
use App\Models\DemandeAdhesion;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TechnicienDemandesAdhesionWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalDemandes = DemandeAdhesion::count();
        $enAttente = DemandeAdhesion::where('statut', StatutDemandeAdhesionEnum::EN_ATTENTE->value)->count();
        $validees = DemandeAdhesion::where('statut', StatutDemandeAdhesionEnum::VALIDEE->value)->count();
        $rejetees = DemandeAdhesion::where('statut', StatutDemandeAdhesionEnum::REJETEE->value)->count();

        return [
            Stat::make('Total Demandes', $totalDemandes)
                ->description('Nombre total de demandes')
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

