<?php

namespace App\Filament\Widgets;

use App\Enums\StatutDemandeAdhesionEnum;
use App\Enums\TypeDemandeurEnum;
use App\Models\DemandeAdhesion;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class MedecinControleurDemandesStatutWidget extends ChartWidget
{
    protected ?string $heading = 'Demandes par Statut';

    protected function getData(): array
    {
        $data = DemandeAdhesion::where('type_demandeur', TypeDemandeurEnum::PRESTATAIRE->value)
            ->select('statut', DB::raw('COUNT(*) as total'))
            ->groupBy('statut')
            ->pluck('total', 'statut')
            ->toArray();

        $labels = [];
        $values = [];
        $colors = [];

        foreach (StatutDemandeAdhesionEnum::cases() as $statut) {
            $count = $data[$statut->value] ?? 0;
            if ($count > 0) {
                $labels[] = $statut->getLabel();
                $values[] = $count;
                $colors[] = match($statut) {
                    StatutDemandeAdhesionEnum::EN_ATTENTE => 'rgba(245, 158, 11, 0.8)',
                    StatutDemandeAdhesionEnum::VALIDEE => 'rgba(16, 185, 129, 0.8)',
                    StatutDemandeAdhesionEnum::REJETEE => 'rgba(239, 68, 68, 0.8)',
                    StatutDemandeAdhesionEnum::PROPOSEE => 'rgba(59, 130, 246, 0.8)',
                    StatutDemandeAdhesionEnum::ACCEPTEE => 'rgba(16, 185, 129, 0.8)',
                };
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Nombre de demandes',
                    'data' => $values,
                    'backgroundColor' => $colors,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}

