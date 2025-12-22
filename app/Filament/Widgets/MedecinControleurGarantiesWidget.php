<?php

namespace App\Filament\Widgets;

use App\Models\CategorieGarantie;
use App\Models\Garantie;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Filament\Facades\Filament;


class MedecinControleurGarantiesWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $user = Auth::user() ?? Filament::auth()->user();
        
        if (!$user || !$user->personnel) {
            return [
                Stat::make('Total Garanties', 0)
                    ->description('Aucune garantie')
                    ->color('gray'),
            ];
        }

        $medecinControleur = $user->personnel;

        // Filtrer les garanties créées par ce médecin contrôleur
        $baseQuery = Garantie::where('medecin_controleur_id', $medecinControleur->id);

        $totalGaranties = $baseQuery->count();
        $garantiesActives = (clone $baseQuery)->where('est_active', true)->count();
        $categories = CategorieGarantie::where('medecin_controleur_id', $medecinControleur->id)->count();

        return [
            Stat::make('Total Garanties', $totalGaranties)
                ->description('Nombre total de garanties')
                ->descriptionIcon('heroicon-o-shield-check')
                ->color('primary'),

            Stat::make('Garanties Actives', $garantiesActives)
                ->description('Garanties actuellement actives')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Catégories', $categories)
                ->description('Nombre de catégories de garanties')
                ->descriptionIcon('heroicon-o-folder')
                ->color('info'),
        ];
    }
}

