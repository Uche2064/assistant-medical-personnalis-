<?php

namespace App\Filament\Widgets;

use App\Models\Garantie;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Facades\Filament;


class MedecinControleurTopGarantiesWidget extends TableWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        $user = Auth::user() ?? Filament::auth()->user();
        
        if (!$user || !$user->personnel) {
            return $table
                ->query(fn (): Builder => Garantie::query()->whereRaw('1 = 0'))
                ->columns([]);
        }

        $medecinControleur = $user->personnel;

        // Récupérer les garanties pour calculer les positions
        $garanties = Garantie::where('medecin_controleur_id', $medecinControleur->id)
            ->where('est_active', true)
            ->orderBy('plafond', 'desc')
            ->limit(5)
            ->get();
        
        $positions = $garanties->pluck('id')->flip()->toArray();

        return $table
            ->query(
                fn (): Builder => Garantie::query()
                    ->where('medecin_controleur_id', $medecinControleur->id)
                    ->where('est_active', true)
                    ->orderBy('plafond', 'desc')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('position')
                    ->label('Position')
                    ->getStateUsing(function ($record) use ($positions) {
                        $position = ($positions[$record->id] ?? 0) + 1;
                        return '#' . $position;
                    })
                    ->weight('bold'),

                TextColumn::make('libelle')
                    ->label('Libellé')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('plafond')
                    ->label('Plafond')
                    ->formatStateUsing(fn ($state) => number_format($state, 0, ',', ' ') . ' FCFA')
                    ->sortable(),

                TextColumn::make('prix_standard')
                    ->label('Prix Standard')
                    ->formatStateUsing(fn ($state) => number_format($state, 0, ',', ' ') . ' FCFA')
                    ->sortable(),

                TextColumn::make('taux_couverture')
                    ->label('Taux Couverture')
                    ->formatStateUsing(fn ($state) => number_format($state, 2, ',', ' ') . '%')
                    ->sortable(),

                TextColumn::make('coverage_amount')
                    ->label('Montant Couverture')
                    ->getStateUsing(fn ($record) => $record->coverage_amount)
                    ->formatStateUsing(fn ($state) => number_format($state, 0, ',', ' ') . ' FCFA'),

                TextColumn::make('statut')
                    ->label('Statut')
                    ->getStateUsing(fn ($record) => $record->est_active ? 'Active' : 'Inactive')
                    ->badge()
                    ->color(fn ($record) => $record->est_active ? 'success' : 'gray'),
            ])
            ->heading('Top Garanties')
            ->defaultSort('plafond', 'desc')
            ->emptyStateHeading('Aucune garantie')
            ->emptyStateDescription('Vos garanties apparaîtront ici.');
    }
}

