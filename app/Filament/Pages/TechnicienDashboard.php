<?php

namespace App\Filament\Pages;

use App\Enums\RoleEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class TechnicienDashboard extends Page
{
    protected static ?string $navigationLabel = 'Tableau de bord';

    protected static ?string $title = 'Tableau de bord Technicien';

    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::check() && Auth::user()->hasRole(RoleEnum::TECHNICIEN->value);
    }

    public function mount(): void
    {
        $user = Auth::user();

        if (!$user->hasRole(RoleEnum::TECHNICIEN->value)) {
            abort(403, 'Accès non autorisé');
        }
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\TechnicienDemandesAdhesionWidget::class,
            \App\Filament\Widgets\TechnicienClientsWidget::class,
            \App\Filament\Widgets\TechnicienPropositionsContratWidget::class,
            \App\Filament\Widgets\TechnicienFacturesWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            \App\Filament\Widgets\TechnicienClientsEvolutionWidget::class,
        ];
    }
}

