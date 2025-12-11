<?php

namespace App\Filament\Pages;

use App\Enums\RoleEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class AdminGlobalDashboard extends Page
{
    protected static ?string $navigationLabel = 'Tableau de bord';

    protected static ?string $title = 'Tableau de bord Administrateur';

    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::check() && Auth::user()->hasRole(RoleEnum::ADMIN_GLOBAL->value);
    }

    public function mount(): void
    {
        $user = Auth::user();

        if (!$user->hasRole(RoleEnum::ADMIN_GLOBAL->value)) {
            abort(403, 'Accès non autorisé');
        }
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\PersonnelStatsWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            \App\Filament\Widgets\PersonnelEvolutionWidget::class,
            \App\Filament\Widgets\PersonnelRepartitionRoleWidget::class,
            \App\Filament\Widgets\PersonnelRepartitionSexeWidget::class,
            \App\Filament\Widgets\DerniersPersonnelsWidget::class,
        ];
    }
}

