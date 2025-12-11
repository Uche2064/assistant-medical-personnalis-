<?php

namespace App\Filament\Widgets;

use App\Enums\RoleEnum;
use App\Models\CommercialParrainageCode;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class CommercialStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        // Utiliser le guard Filament pour obtenir l'utilisateur
        $commercial = Filament::auth()->user() ?? Auth::guard('web')->user();

        if (!$commercial) {
            return [
                Stat::make('Total Clients', 0)
                    ->description('Aucun client')
                    ->color('gray'),
                Stat::make('Code Parrainage', 'Aucun code')
                    ->description('Aucun code actif')
                    ->color('gray'),
            ];
        }

        // Nombre de clients - utiliser directement commercial_id sans whereHas pour éviter les problèmes
        $totalClients = \App\Models\Client::where('commercial_id', $commercial->id)
            ->whereHas('user.roles', fn($q) => $q->where('name', RoleEnum::CLIENT->value))
            ->count();

        // Code parrainage actuel - utiliser la relation parrainageCodes du modèle User
        $allCodes = $commercial->parrainageCodes()
            ->orderBy('created_at', 'desc')
            ->get();

        // Chercher d'abord un code actif et non expiré
        $currentCode = $allCodes->first(function ($code) {
            return $code->est_actif && !$code->isExpired();
        });

        // Si aucun code actif, prendre le dernier code (même expiré ou inactif)
        if (!$currentCode && $allCodes->isNotEmpty()) {
            $currentCode = $allCodes->first();
        }

        $codeParrainage = $currentCode ? $currentCode->code_parrainage : 'Aucun code';
        $joursRestants = $currentCode ? (int) round(now()->diffInDays($currentCode->date_expiration, false)) : 0;

        return [
            Stat::make('Total Clients', $totalClients)
                ->description('Clients ajoutés par vous')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),

            Stat::make('Code Parrainage', $codeParrainage)
                ->description($currentCode
                    ? ($joursRestants > 0 ? "Expire dans " . number_format($joursRestants, 0, ',', ' ') . " jour" . ($joursRestants > 1 ? 's' : '') : "Expiré")
                    : "Aucun code actif")
                ->descriptionIcon('heroicon-m-ticket')
                ->color($currentCode && $joursRestants > 0 ? 'success' : 'warning'),
        ];
    }
}
