<?php

namespace App\Filament\Resources\Garanties\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class GarantiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('libelle')
                    ->label('Libellé')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('categorieGarantie.libelle')
                    ->label('Catégorie')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('plafond')
                    ->label('Plafond')
                    ->money('XOF')
                    ->sortable(),

                TextColumn::make('prix_standard')
                    ->label('Prix standard')
                    ->money('XOF')
                    ->sortable(),

                TextColumn::make('taux_couverture')
                    ->label('Taux couverture')
                    ->formatStateUsing(fn ($state) => $state . '%')
                    ->sortable(),

                IconColumn::make('est_active')
                    ->label('Actif')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('est_active')
                    ->label('Statut')
                    ->options([
                        1 => 'Actif',
                        0 => 'Inactif',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->color('info'),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}

