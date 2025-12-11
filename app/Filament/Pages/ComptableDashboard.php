<?php

namespace App\Filament\Pages;

use App\Enums\RoleEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class ComptableDashboard extends Page
{
    protected static ?string $navigationLabel = 'Tableau de bord';

    protected static ?string $title = 'Tableau de bord Comptable';

    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::check() && Auth::user()->hasRole(RoleEnum::COMPTABLE->value);
    }

    public function mount(): void
    {
        $user = Auth::user();

        if (!$user->hasRole(RoleEnum::COMPTABLE->value)) {
            abort(403, 'Accès non autorisé');
        }
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // Widgets spécifiques au comptable (à créer)
            \App\Filament\Widgets\ComptableStatsWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            // Widgets spécifiques au comptable (à créer)
        ];
    }
}

