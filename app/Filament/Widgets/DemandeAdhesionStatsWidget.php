<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class DemandeAdhesionStatsWidget extends StatsOverviewWidget
{
    public ?\App\Models\DemandeAdhesion $record = null;

    protected function getStats(): array
    {
        // Récupérer la demande depuis la propriété ou depuis getWidgetData()
        $record = $this->record;

        // Si pas de record dans la propriété, essayer de le récupérer depuis getWidgetData()
        if (!$record) {
            $widgetData = $this->getWidgetData();
            $record = $widgetData['record'] ?? null;
        }

        // Si toujours pas de record, essayer de le récupérer depuis l'ID dans l'URL
        if (!$record) {
            $request = request();
            $recordId = $request->route('record');

            if ($recordId) {
                $record = \App\Models\DemandeAdhesion::find($recordId);
            }
        }

        if (!$record) {
            return [];
        }

        // Charger les relations nécessaires
        $demande = $record->load([
            'assurePrincipal.beneficiaires.user.personne',
            'user.personne',
        ]);

        // Collecter toutes les personnes (assuré principal + bénéficiaires)
        $toutesLesPersonnes = collect();

        // Ajouter l'assuré principal
        if ($demande->user && $demande->user->personne) {
            $toutesLesPersonnes->push($demande->user->personne);
        }

        // Ajouter les bénéficiaires
        if ($demande->assurePrincipal) {
            foreach ($demande->assurePrincipal->beneficiaires as $beneficiaire) {
                if ($beneficiaire->user && $beneficiaire->user->personne) {
                    $toutesLesPersonnes->push($beneficiaire->user->personne);
                }
            }
        }

        // Calculer les statistiques
        // Le total inclut l'assuré principal + les bénéficiaires
        $totalBeneficiaires = $toutesLesPersonnes->count();

        // Répartition par sexe
        $hommes = $toutesLesPersonnes->where('sexe', 'M')->count();
        $femmes = $toutesLesPersonnes->where('sexe', 'F')->count();

        // Répartition par âge
        $repartitionAge = [
            '0-18' => 0,
            '19-30' => 0,
            '31-50' => 0,
            '51-65' => 0,
            '65+' => 0,
        ];

        foreach ($toutesLesPersonnes as $personne) {
            if ($personne->date_naissance) {
                $age = Carbon::parse($personne->date_naissance)->diffInYears(now());

                if ($age <= 18) {
                    $repartitionAge['0-18']++;
                } elseif ($age <= 30) {
                    $repartitionAge['19-30']++;
                } elseif ($age <= 50) {
                    $repartitionAge['31-50']++;
                } elseif ($age <= 65) {
                    $repartitionAge['51-65']++;
                } else {
                    $repartitionAge['65+']++;
                }
            }
        }

        $ageMax = $repartitionAge['65+'];
        $ageMaxLabel = '65+ ans';

        return [
            Stat::make('Total bénéficiaires', $totalBeneficiaires)
                ->description('Incluant l\'assuré principal')
                ->icon('heroicon-o-users')
                ->color('info'),

            Stat::make('Répartition par sexe', "H: {$hommes} | F: {$femmes}")
                ->description('Hommes et femmes')
                ->icon('heroicon-o-user-group')
                ->color('success'),

            Stat::make('Répartition par âge',
                collect($repartitionAge)
                    ->filter(fn($count) => $count > 0)
                    ->map(fn($count, $tranche) => "{$tranche}: {$count}")
                    ->join(', ')
                    ?: 'Aucune donnée'
            )
                ->description('Tranches d\'âge')
                ->icon('heroicon-o-calendar')
                ->color('warning'),
        ];
    }
}
