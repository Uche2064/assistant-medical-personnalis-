<?php

namespace App\Filament\Widgets;

use App\Enums\TypeDemandeurEnum;
use App\Models\DemandeAdhesion;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class MedecinControleurDemandesEvolutionWidget extends ChartWidget
{
    protected ?string $heading = 'Évolution mensuelle des demandes prestataires';

    protected function getData(): array
    {
        // Récupérer les données des 12 derniers mois pour les demandes prestataires
        $data = DemandeAdhesion::where('type_demandeur', TypeDemandeurEnum::PRESTATAIRE->value)
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
                    'label' => 'Demandes prestataires',
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

