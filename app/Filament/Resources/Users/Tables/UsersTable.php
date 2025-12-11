<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('personne.nom')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('personne.prenoms')
                    ->label('Prénoms')
                    ->searchable(),

                TextColumn::make('contact')
                    ->label('Contact')
                    ->searchable(),

                TextColumn::make('roles.name')
                    ->label('Rôles')
                    ->badge()
                    ->separator(','),

                IconColumn::make('est_actif')
                    ->label('Actif')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('solde')
                    ->label('Solde')
                    ->numeric()
                    ->money('XOF')
                    ->sortable(),

                IconColumn::make('mot_de_passe_a_changer')
                    ->label('MDP à changer')
                    ->boolean(),

                TextColumn::make('email_verifier_a')
                    ->label('Email vérifié')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),

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
                SelectFilter::make('roles')
                    ->label('Rôle')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
