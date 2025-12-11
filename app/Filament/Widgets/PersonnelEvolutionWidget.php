<?php

namespace App\Filament\Widgets;

use App\Enums\RoleEnum;
use App\Models\Personnel;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PersonnelEvolutionWidget extends ChartWidget
{
    protected ?string $heading = 'Évolution mensuelle du personnel';

    protected function getData(): array
    {
        $user = Auth::user();
        $isGestionnaire = $user->hasRole(RoleEnum::GESTIONNAIRE->value);

        // Récupérer les personnels selon le rôle
        $query = Personnel::query()
            ->whereHas('user', function ($userQuery) {
                $userQuery->whereHas('roles', function ($roleQuery) {
                    $roleQuery->whereNotIn('name', [RoleEnum::ADMIN_GLOBAL->value]);
                });
            });

        // Si gestionnaire, filtrer par gestionnaire_id
        if ($isGestionnaire && $user->personnel) {
            $query->where('gestionnaire_id', $user->personnel->id);
        }

        // Récupérer les données des 12 derniers mois
        // Utiliser TO_CHAR pour PostgreSQL au lieu de DATE_FORMAT (MySQL)
        $data = $query
            ->select(
                DB::raw("TO_CHAR(created_at, 'YYYY-MM') as mois"),
                DB::raw('COUNT(*) as total')
            )
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy(DB::raw("TO_CHAR(created_at, 'YYYY-MM')"))
            ->orderBy('mois')
            ->pluck('total', 'mois')
            ->toArray();

        // Remplir les mois manquants avec 0
        $labels = [];
        $values = [];
        for ($i = 11; $i >= 0; $i--) {
            $mois = now()->subMonths($i)->format('Y-m');
            $labels[] = now()->subMonths($i)->format('M Y');
            $values[] = $data[$mois] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Personnel ajouté',
                    'data' => $values,
                    'backgroundColor' => 'rgba(199, 24, 62, 0.2)',
                    'borderColor' => 'rgba(199, 24, 62, 1)',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
