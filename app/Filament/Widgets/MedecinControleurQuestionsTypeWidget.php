<?php

namespace App\Filament\Widgets;

use App\Enums\TypeDonneeEnum;
use App\Models\Question;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class MedecinControleurQuestionsTypeWidget extends ChartWidget
{
    protected ?string $heading = 'Questions par Type';

    protected function getData(): array
    {
        $data = Question::select('type_de_donnee', DB::raw('COUNT(*) as total'))
            ->groupBy('type_de_donnee')
            ->pluck('total', 'type_de_donnee')
            ->toArray();

        $labels = [];
        $values = [];
        $colors = [
            'rgba(199, 24, 62, 0.8)',
            'rgba(59, 130, 246, 0.8)',
            'rgba(16, 185, 129, 0.8)',
            'rgba(245, 158, 11, 0.8)',
            'rgba(139, 92, 246, 0.8)',
            'rgba(236, 72, 153, 0.8)',
            'rgba(20, 184, 166, 0.8)',
            'rgba(251, 146, 60, 0.8)',
        ];

        $colorIndex = 0;
        foreach (TypeDonneeEnum::cases() as $type) {
            $count = $data[$type->value] ?? 0;
            if ($count > 0) {
                $labels[] = $type->getLabel();
                $values[] = $count;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Nombre de questions',
                    'data' => $values,
                    'backgroundColor' => array_slice($colors, 0, count($values)),
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

