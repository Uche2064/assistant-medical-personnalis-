<?php

namespace App\Filament\Resources\Assures\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AssuresTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.personne.nom')
                    ->label('Nom')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn ($state, $record) => $state ?? $record->user->email),

                TextColumn::make('user.personne.prenoms')
                    ->label('Prénoms')
                    ->searchable(),

                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-envelope'),

                TextColumn::make('est_principal')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Principal' : 'Bénéficiaire')
                    ->color(fn ($state) => $state ? 'success' : 'info')
                    ->sortable(),

                TextColumn::make('lien_parente')
                    ->label('Lien parenté')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? $state?->getLabel() : 'N/A')
                    ,

                TextColumn::make('assurePrincipal.user.personne.nom')
                    ->label('Assuré Principal')
                    ->searchable()
                    ->formatStateUsing(fn ($state, $record) => $state ? $state . ' ' . ($record->assurePrincipal->user->personne->prenoms ?? '') : '-')
                    ,

                TextColumn::make('beneficiaires_count')
                    ->label('Nb Bénéficiaires')
                    ->counts('beneficiaires')
                    ->badge()
                    ->color('warning')
                    ,

                TextColumn::make('user.contact')
                    ->label('Contact')
                    ->icon('heroicon-o-phone')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('est_principal')
                    ->label('Type')
                    ->options([
                        true => 'Assuré Principal',
                        false => 'Bénéficiaire',
                    ])
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}

