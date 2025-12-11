<?php

namespace App\Filament\Resources\CommercialParrainageCodes\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class CommercialParrainageCodesTable
{
    public static function make(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                // Filtrer pour n'afficher que les codes du commercial connecté
                $commercial = Auth::user();
                if ($commercial) {
                    $query->where('commercial_id', $commercial->id);
                }
            })
            ->columns([
                TextColumn::make('code_parrainage')
                    ->label('Code Parrainage')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Code copié!')
                    ->weight('bold')
                    ->color('primary'),

                TextColumn::make('date_debut')
                    ->label('Date de début')
                    ->dateTime('d/m/Y à H:i')
                    ->sortable(),

                TextColumn::make('date_expiration')
                    ->label('Date d\'expiration')
                    ->dateTime('d/m/Y à H:i')
                    ->sortable()
                    ->color(function ($record) {
                        return $record->isExpired() ? 'danger' : 'success';
                    }),

                TextColumn::make('jours_restants')
                    ->label('Jours restants')
                    ->getStateUsing(function ($record) {
                        if ($record->isExpired()) {
                            return 'Expiré';
                        }
                        $jours = now()->diffInDays($record->date_expiration, false);
                        return $jours > 0 ? $jours . ' jours' : 'Expiré';
                    })
                    ->color(function ($record) {
                        if ($record->isExpired()) {
                            return 'danger';
                        }
                        $jours = now()->diffInDays($record->date_expiration, false);
                        return $jours <= 30 ? 'warning' : 'success';
                    }),

                BooleanColumn::make('est_actif')
                    ->label('Actif')
                    ->sortable(),

                BooleanColumn::make('est_renouvele')
                    ->label('Renouvelé')
                    ->sortable(),

                TextColumn::make('statut')
                    ->label('Statut')
                    ->getStateUsing(function ($record) {
                        if ($record->est_actif && !$record->isExpired()) {
                            return 'Actif';
                        }
                        if ($record->isExpired()) {
                            return 'Expiré';
                        }
                        return 'Inactif';
                    })
                    ->badge()
                    ->color(function ($record) {
                        if ($record->est_actif && !$record->isExpired()) {
                            return 'success';
                        }
                        if ($record->isExpired()) {
                            return 'danger';
                        }
                        return 'gray';
                    }),

                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y à H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Aucun code parrainage')
            ->emptyStateDescription('Vos codes de parrainage apparaîtront ici une fois générés.');
    }
}

