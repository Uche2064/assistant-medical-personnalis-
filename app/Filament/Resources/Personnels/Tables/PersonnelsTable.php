<?php

namespace App\Filament\Resources\Personnels\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PersonnelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.personne.nom')
                    ->label('Nom')
                    ->searchable(),

                TextColumn::make('user.personne.prenoms')
                    ->label('Prénoms')
                    ->searchable(),

                TextColumn::make('user.roles.name')
                    ->label('Rôles')
                    ->badge()
                    ->separator(','),

                TextColumn::make('user.personne.sexe')
                    ->label('Sexe')
                    ->formatStateUsing(fn ($state) => $state ? ($state === 'M' ? 'Masculin' : 'Féminin') : '-')
                    ->badge()
                    ->color(fn ($state) => $state === 'M' ? 'info' : 'primary'),

                TextColumn::make('user.personne.date_naissance')
                    ->label('Date de naissance')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                // EditAction retiré - l'admin ne peut pas modifier le personnel
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
