<?php

namespace App\Filament\Resources\TypeContrats\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TypeContratsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('libelle')
                    ->label('Libellé')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('prime_standard')
                    ->label('Prime standard')
                    ->money('XOF')
                    ->sortable(),

                TextColumn::make('technicien.user.email')
                    ->label('Technicien')
                    ->searchable()
                    ->sortable(),

                IconColumn::make('est_actif')
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
                SelectFilter::make('est_actif')
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

