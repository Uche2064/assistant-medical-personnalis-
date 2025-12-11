<?php

namespace App\Filament\Resources\ClientPrestataires\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ClientPrestatairesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('clientContrat.client.user.email')
                    ->label('Client')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('prestataire.user.email')
                    ->label('Prestataire')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type_prestataire')
                    ->label('Type prestataire')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'pharmacie' => 'Pharmacie',
                        'centre_soins' => 'Centre de Soins',
                        'optique' => 'Optique',
                        'laboratoire_centre_diagnostic' => 'Laboratoire et centre de diagnostic',
                        default => ucfirst($state),
                    })
                    ->color('info')
                    ->searchable(),

                TextColumn::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'actif' => 'Actif',
                        'inactif' => 'Inactif',
                        default => ucfirst($state),
                    })
                    ->color(fn ($state) => match($state) {
                        'actif' => 'success',
                        'inactif' => 'danger',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('statut')
                    ->label('Statut')
                    ->options([
                        'actif' => 'Actif',
                        'inactif' => 'Inactif',
                    ])
                    ->multiple()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}

