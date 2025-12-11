<?php

namespace App\Filament\Widgets;

use App\Enums\RoleEnum;
use App\Models\Personnel;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PersonnelRepartitionRoleWidget extends ChartWidget
{
    protected ?string $heading = 'Répartition par rôle';

    protected function getData(): array
    {
        $user = Auth::user();
        $isAdminGlobal = $user->hasRole(RoleEnum::ADMIN_GLOBAL->value);
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

        // Rôles à afficher
        // Admin global voit tous les rôles (y compris gestionnaire)
        // Gestionnaire ne voit que les rôles qu'il gère
        $roles = [
            RoleEnum::COMPTABLE->value,
            RoleEnum::TECHNICIEN->value,
            RoleEnum::MEDECIN_CONTROLEUR->value,
            RoleEnum::COMMERCIAL->value,
        ];

        if ($isAdminGlobal) {
            $roles[] = RoleEnum::GESTIONNAIRE->value;
        }

        $data = $query
            ->join('users', 'personnels.user_id', '=', 'users.id')
            ->join('model_has_roles', function ($join) {
                $join->on('users.id', '=', 'model_has_roles.model_id')
                     ->where('model_has_roles.model_type', '=', 'App\Models\User');
            })
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->whereIn('roles.name', $roles)
            ->select('roles.name as role', DB::raw('COUNT(DISTINCT personnels.id) as total'))
            ->groupBy('roles.name')
            ->pluck('total', 'role')
            ->toArray();

        $labels = [];
        $values = [];
        $colors = [
            RoleEnum::GESTIONNAIRE->value => 'rgba(239, 68, 68, 0.8)',
            RoleEnum::COMPTABLE->value => 'rgba(34, 197, 94, 0.8)',
            RoleEnum::TECHNICIEN->value => 'rgba(59, 130, 246, 0.8)',
            RoleEnum::MEDECIN_CONTROLEUR->value => 'rgba(168, 85, 247, 0.8)',
            RoleEnum::COMMERCIAL->value => 'rgba(245, 158, 11, 0.8)',
        ];

        $backgroundColors = [];
        foreach ($roles as $role) {
            $label = RoleEnum::getLabel($role);
            $labels[] = $label;
            $values[] = $data[$role] ?? 0;
            $backgroundColors[] = $colors[$role] ?? 'rgba(156, 163, 175, 0.8)';
        }

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
        return 'doughnut';
    }
}
