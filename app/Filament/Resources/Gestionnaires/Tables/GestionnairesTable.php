<?php

namespace App\Filament\Resources\Gestionnaires\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GestionnairesTable
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
                
                TextColumn::make('user.contact')
                    ->label('Contact')
                    ->searchable(),
                
                IconColumn::make('user.est_actif')
                    ->label('Actif')
                    ->boolean()
                    ->sortable(),
                
                TextColumn::make('user.created_at')
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
                // Pas d'EditAction - admin_global ne peut pas modifier
            ])
            ->defaultSort('user.created_at', 'desc');
    }
}

