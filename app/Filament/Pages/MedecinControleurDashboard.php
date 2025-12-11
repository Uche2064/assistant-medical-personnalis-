<?php

namespace App\Filament\Pages;

use App\Enums\RoleEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class MedecinControleurDashboard extends Page
{
    protected static ?string $navigationLabel = 'Tableau de bord';

    protected static ?string $title = 'Tableau de bord Médecin Contrôleur';

    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::check() && Auth::user()->hasRole(RoleEnum::MEDECIN_CONTROLEUR->value);
    }

    public function mount(): void
    {
        $user = Auth::user();

        if (!$user->hasRole(RoleEnum::MEDECIN_CONTROLEUR->value)) {
            abort(403, 'Accès non autorisé');
        }
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Widgets\MedecinControleurQuestionsWidget::class,
            \App\Filament\Widgets\MedecinControleurDemandesPrestatairesWidget::class,
            \App\Filament\Widgets\MedecinControleurGarantiesWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            \App\Filament\Widgets\MedecinControleurDemandesEvolutionWidget::class,
            \App\Filament\Widgets\MedecinControleurQuestionsTypeWidget::class,
            \App\Filament\Widgets\MedecinControleurDemandesStatutWidget::class,
            \App\Filament\Widgets\MedecinControleurTopGarantiesWidget::class,
        ];
    }
}

