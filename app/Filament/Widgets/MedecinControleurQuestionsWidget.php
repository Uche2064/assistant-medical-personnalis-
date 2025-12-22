<?php

namespace App\Filament\Widgets;

use App\Models\Question;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Filament\Facades\Filament;

class MedecinControleurQuestionsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $user = Auth::user() ?? Filament::auth()->user();
        
        if (!$user || !$user->personnel) {
            return [
                Stat::make('Total Questions', 0)
                    ->description('Aucune question')
                    ->color('gray'),
            ];
        }

        $totalQuestions = Question::count();
        $questionsActives = Question::where('est_active', true)->count();
        $questionsObligatoires = Question::where('est_obligatoire', true)->count();
        $tauxActivation = $totalQuestions > 0 ? round(($questionsActives / $totalQuestions) * 100, 1) : 0;

        return [
            Stat::make('Total Questions', $totalQuestions)
                ->description('Nombre total de questions')
                ->descriptionIcon('heroicon-o-question-mark-circle')
                ->color('primary'),

            Stat::make('Questions Actives', $questionsActives)
                ->description('Questions actuellement actives')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Obligatoires', $questionsObligatoires)
                ->description('Questions obligatoires')
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color('warning'),

            Stat::make('Taux d\'Activation', $tauxActivation . '%')
                ->description('Pourcentage de questions actives')
                ->descriptionIcon('heroicon-o-chart-bar')
                ->color('info'),
        ];
    }
}

