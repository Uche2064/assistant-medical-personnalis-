<?php

namespace App\Filament\Resources\Assures\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AssuresTable
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
                
                IconColumn::make('est_principal')
                    ->label('Principal')
                    ->boolean()
                    ->sortable(),
                
                TextColumn::make('lien_parente')
                    ->label('Lien parenté')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->getLabel() ?? 'N/A'),
                
                TextColumn::make('client.user.email')
                    ->label('Client')
                    ->searchable(),
                
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
                EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}

