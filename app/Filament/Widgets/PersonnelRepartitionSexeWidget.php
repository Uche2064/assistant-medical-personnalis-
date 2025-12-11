<?php

namespace App\Filament\Widgets;

use App\Enums\RoleEnum;
use App\Models\Personnel;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PersonnelRepartitionSexeWidget extends ChartWidget
{
    protected ?string $heading = 'Répartition par sexe';

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

        $data = $query
            ->join('users', 'personnels.user_id', '=', 'users.id')
            ->join('personnes', 'users.personne_id', '=', 'personnes.id')
            ->select('personnes.sexe', DB::raw('COUNT(*) as total'))
            ->whereNotNull('personnes.sexe')
            ->groupBy('personnes.sexe')
            ->pluck('total', 'sexe')
            ->toArray();

        $labels = [];
        $values = [];
        $colors = [
            'M' => 'rgba(59, 130, 246, 0.8)',
            'F' => 'rgba(236, 72, 153, 0.8)',
        ];

        $backgroundColors = [];

        // Masculin
        $labels[] = 'Masculin';
        $values[] = $data['M'] ?? 0;
        $backgroundColors[] = $colors['M'];

        // Féminin
        $labels[] = 'Féminin';
        $values[] = $data['F'] ?? 0;
        $backgroundColors[] = $colors['F'];

        return [
            'datasets' => [
                [
                    'label' => 'Nombre de personnels',
                    'data' => $values,
                    'backgroundColor' => $backgroundColors,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
