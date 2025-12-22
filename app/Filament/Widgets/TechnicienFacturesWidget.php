<?php

namespace App\Filament\Widgets;

use App\Models\Facture;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Filament\Facades\Filament;


class TechnicienFacturesWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $user = Auth::user() ?? Filament::auth()->user();
        
        if (!$user || !$user->personnel) {
            return [
                Stat::make('Total', 0)
                    ->description('Aucune facture')
                    ->color('gray'),
            ];
        }

        // Factures à valider (en attente) - toutes les factures en attente
        $aValider = Facture::where('statut', 'en_attente')->count();

        $technicien = $user->personnel;

        // Factures validées par ce technicien
        $validees = Facture::where('statut', 'validee_technicien')
            ->where('technicien_id', $technicien->id)
            ->count();

        // Factures en attente de validation par le médecin (validées par le technicien, en attente du médecin)
        $attenteMedecin = Facture::where('statut', 'validee_technicien')
            ->where('technicien_id', $technicien->id)
            ->count();

        // Total : toutes les factures (en attente + validées par ce technicien)
        $total = Facture::where(function ($query) use ($technicien) {
            $query->where('statut', 'en_attente')
                  ->orWhere(function ($q) use ($technicien) {
                      $q->where('statut', 'validee_technicien')
                        ->where('technicien_id', $technicien->id);
                  });
        })->count();

        return [
            Stat::make('Total', $total)
                ->description('Nombre total de factures')
                ->descriptionIcon('heroicon-o-document-currency-dollar')
                ->color('primary'),

            Stat::make('À Valider', $aValider)
                ->description('Factures en attente de validation')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('Validées', $validees)
                ->description('Factures validées par vous')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Attente Médecin', $attenteMedecin)
                ->description('En attente de validation médecin')
                ->descriptionIcon('heroicon-o-user')
                ->color('info'),
        ];
    }
}

