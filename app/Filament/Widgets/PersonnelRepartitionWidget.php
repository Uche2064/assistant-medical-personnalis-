<?php

namespace App\Filament\Widgets;

use App\Enums\RoleEnum;
use App\Models\Personnel;
use Filament\Widgets\ChartWidget;

class PersonnelRepartitionWidget extends ChartWidget
{
    protected ?string $heading = 'Répartition du Personnel par Rôle';
    
    protected static ?int $sort = 2;
    
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $roles = [
            RoleEnum::GESTIONNAIRE->value,
            RoleEnum::COMMERCIAL->value,
            RoleEnum::MEDECIN_CONTROLEUR->value,
            RoleEnum::TECHNICIEN->value,
            RoleEnum::COMPTABLE->value,
        ];
        
        $data = [];
        $labels = [];
        
        foreach ($roles as $role) {
            $count = Personnel::whereHas('user.roles', function ($q) use ($role) {
                $q->where('name', $role);
            })->count();
            
            $data[] = $count;
            $labels[] = RoleEnum::getLabel($role);
        }
        
        return [
            'datasets' => [
                [
                    'label' => 'Nombre de personnel',
                    'data' => $data,
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.5)',   // Blue
                        'rgba(16, 185, 129, 0.5)',   // Green
                        'rgba(245, 158, 11, 0.5)',   // Yellow
                        'rgba(239, 68, 68, 0.5)',   // Red
                        'rgba(139, 92, 246, 0.5)',   // Purple
                    ],
                    'borderColor' => [
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)',
                        'rgb(245, 158, 11)',
                        'rgb(239, 68, 68)',
                        'rgb(139, 92, 246)',
                    ],
                    'borderWidth' => 1,
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
