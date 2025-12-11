<?php

namespace App\Filament\Resources\ClientContrats\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ClientContratsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero_police')
                    ->label('Numéro de police')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('client.user.email')
                    ->label('Client')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('typeContrat.libelle')
                    ->label('Type de contrat')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('date_debut')
                    ->label('Date de début')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('date_fin')
                    ->label('Date de fin')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('statut')
                    ->label('Statut')
                    ->getStateUsing(fn ($record) => $record->statut->getLabel())
                    ->badge()
                    ->color(fn ($record) => $record->statut->getColor())
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('statut')
                    ->label('Statut')
                    ->options(function () {
                        $options = [];
                        foreach (\App\Enums\StatutContratEnum::cases() as $statut) {
                            $options[$statut->value] = $statut->getLabel();
                        }
                        return $options;
                    })
                    ->multiple()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}

