<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use App\Models\PropositionContrat;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Filament\Facades\Filament;


class TechnicienClientsEvolutionWidget extends ChartWidget
{
    protected ?string $heading = 'Évolution mensuelle des clients';

    protected function getData(): array
    {
        $user = Auth::user() ?? Filament::auth()->user();
        
        if (!$user || !$user->personnel) {
            return [
                'datasets' => [
                    [
                        'label' => 'Clients ajoutés',
                        'data' => array_fill(0, 12, 0),
                        'backgroundColor' => 'rgba(199, 24, 62, 0.2)',
                        'borderColor' => 'rgba(199, 24, 62, 1)',
                        'borderWidth' => 2,
                    ],
                ],
                'labels' => [],
            ];
        }

        $technicien = $user->personnel;

        // Récupérer les clients via les propositions de contrats créées par ce technicien
        $clientIds = PropositionContrat::where('technicien_id', $technicien->id)
            ->whereHas('demandeAdhesion', function ($query) {
                $query->whereHas('user', function ($userQuery) {
                    $userQuery->whereHas('client');
                });
            })
            ->get()
            ->map(function ($proposition) {
                return $proposition->demandeAdhesion->user->client->id ?? null;
            })
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        // Récupérer les données des 12 derniers mois pour ces clients
        $data = Client::whereIn('id', $clientIds)
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
                    'label' => 'Clients ajoutés',
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

