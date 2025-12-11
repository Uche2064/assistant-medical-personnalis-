<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ComptableStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        // TODO: Implémenter les statistiques pour le comptable
        return [
            Stat::make('Statistiques', 'À venir')
                ->description('Widgets en cours de développement')
                ->color('gray'),
        ];
    }
}

