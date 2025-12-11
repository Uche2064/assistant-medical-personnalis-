<?php

namespace App\Filament\Pages;

use App\Enums\RoleEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class CommercialDashboard extends Page
{
    protected static ?string $navigationLabel = 'Tableau de bord';

    protected static ?string $title = 'Tableau de bord Commercial';

    protected static ?int $navigationSort = 1;

    // Supprimer la vue personnalisée pour laisser Filament gérer l'affichage automatique des widgets
    // protected string $view = 'filament.pages.commercial-dashboard';

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::check() && Auth::user()->hasRole(RoleEnum::COMMERCIAL->value);
    }

    public function mount(): void
    {
        $user = Auth::user();

        if (!$user->hasRole(RoleEnum::COMMERCIAL->value)) {
            abort(403, 'Accès non autorisé');
        }
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\CommercialStatsWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            \App\Filament\Widgets\DerniersClientsWidget::class,
        ];
    }
}
