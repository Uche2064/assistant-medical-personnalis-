<?php

namespace App\Filament\Widgets;

use App\Enums\StatutPropositionContratEnum;
use App\Models\PropositionContrat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class TechnicienPropositionsContratWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $user = Auth::user();

        if (!$user || !$user->personnel) {
            return [
                Stat::make('Total', 0)
                    ->description('Aucune proposition')
                    ->color('gray'),
            ];
        }

        $technicien = $user->personnel;

        $baseQuery = PropositionContrat::where('technicien_id', $technicien->id);

        $total = $baseQuery->count();
        $proposees = (clone $baseQuery)->where('statut', StatutPropositionContratEnum::PROPOSEE->value)->count();
        $acceptees = (clone $baseQuery)->where('statut', StatutPropositionContratEnum::ACCEPTEE->value)->count();
        $refusees = (clone $baseQuery)->where('statut', StatutPropositionContratEnum::REFUSEE->value)->count();
        $expirees = (clone $baseQuery)->where('statut', StatutPropositionContratEnum::EXPIREE->value)->count();

        return [
            Stat::make('Total', $total)
                ->description('Nombre total de propositions')
                ->descriptionIcon('heroicon-o-document-duplicate')
                ->color('primary'),

            Stat::make('Proposées', $proposees)
                ->description('Propositions en attente')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('Acceptées', $acceptees)
                ->description('Propositions acceptées')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Refusées', $refusees)
                ->description('Propositions refusées')
                ->descriptionIcon('heroicon-o-x-circle')
                ->color('danger'),

            Stat::make('Expirées', $expirees)
                ->description('Propositions expirées')
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color('gray'),
        ];
    }
}

