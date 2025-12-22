<?php

namespace App\Filament\Widgets;

use App\Enums\RoleEnum;
use App\Models\Personnel;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Filament\Facades\Filament;


class PersonnelStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $user = Auth::user() ?? Filament::auth()->user();
        $isAdminGlobal = $user->hasRole(RoleEnum::ADMIN_GLOBAL->value);
        $isGestionnaire = $user->hasRole(RoleEnum::GESTIONNAIRE->value);

        // Récupérer les personnels selon le rôle
        // Un personnel doit avoir un user avec des rôles (sauf admin_global)
        $query = Personnel::query()
            ->whereHas('user', function ($userQuery) {
                $userQuery->whereHas('roles', function ($roleQuery) {
                    $roleQuery->whereNotIn('name', [RoleEnum::ADMIN_GLOBAL->value]);
                });
            });

        // Si gestionnaire, filtrer par gestionnaire_id
        // Si admin global, voir tous les personnels (même ceux sans gestionnaire_id)
        if ($isGestionnaire) {
            $personnel = $user->personnel;
            if (!$personnel) {
                // Si le gestionnaire n'a pas de personnel associé, retourner 0
                return [
                    Stat::make('Total des personnels', 0)
                        ->description('Aucun personnel trouvé')
                        ->descriptionIcon('heroicon-o-users')
                        ->color('gray'),
                    Stat::make('Personnel actif', 0)
                        ->description('Aucun personnel actif')
                        ->descriptionIcon('heroicon-o-check-circle')
                        ->color('gray'),
                    Stat::make('Personnel inactif', 0)
                        ->description('Aucun personnel inactif')
                        ->descriptionIcon('heroicon-o-x-circle')
                        ->color('gray'),
                ];
            }
            $query->where('gestionnaire_id', $personnel->id);
        }

        $totalPersonnel = $query->count();

        $actifQuery = Personnel::query()
            ->whereHas('user', function ($userQuery) {
                $userQuery->where('est_actif', true)
                    ->whereHas('roles', function ($roleQuery) {
                        $roleQuery->whereNotIn('name', [RoleEnum::ADMIN_GLOBAL->value]);
                    });
            });

        if ($isGestionnaire && $user->personnel) {
            $actifQuery->where('gestionnaire_id', $user->personnel->id);
        }

        $personnelActif = $actifQuery->count();

        $inactifQuery = Personnel::query()
            ->whereHas('user', function ($userQuery) {
                $userQuery->where('est_actif', false)
                    ->whereHas('roles', function ($roleQuery) {
                        $roleQuery->whereNotIn('name', [RoleEnum::ADMIN_GLOBAL->value]);
                    });
            });

        if ($isGestionnaire && $user->personnel) {
            $inactifQuery->where('gestionnaire_id', $user->personnel->id);
        }

        $personnelInactif = $inactifQuery->count();

        return [
            Stat::make('Total des personnels', $totalPersonnel)
                ->description('Nombre total de personnels')
                ->descriptionIcon('heroicon-o-users')
                ->color('primary'),

            Stat::make('Personnel actif', $personnelActif)
                ->description('Personnels actifs')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Personnel inactif', $personnelInactif)
                ->description('Personnels inactifs')
                ->descriptionIcon('heroicon-o-x-circle')
                ->color('danger'),
        ];
    }
}

