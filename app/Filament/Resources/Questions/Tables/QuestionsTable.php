<?php

namespace App\Filament\Resources\Questions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class QuestionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('libelle')
                    ->label('Libellé')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->libelle),

                TextColumn::make('type_de_donnee')
                    ->label('Type')
                    ->getStateUsing(fn ($record) => $record->type_de_donnee->getLabel())
                    ->sortable(),

                TextColumn::make('destinataire')
                    ->label('Destinataire')
                    ->searchable()
                    ->sortable(),

                IconColumn::make('est_obligatoire')
                    ->label('Obligatoire')
                    ->boolean()
                    ->sortable(),

                IconColumn::make('est_active')
                    ->label('Active')
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
                SelectFilter::make('est_obligatoire')
                    ->label('Obligatoire')
                    ->options([
                        1 => 'Oui',
                        0 => 'Non',
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

