<?php

namespace App\Filament\Resources\Sinistres\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SinistresTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('description')
                    ->label('Description')
                    ->searchable()
                    ->limit(50)
                    ->sortable(),

                TextColumn::make('assure.user.email')
                    ->label('Assuré')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('prestataire.user.email')
                    ->label('Prestataire')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'en_cours' => 'En cours',
                        'cloture' => 'Clôturé',
                        default => ucfirst($state),
                    })
                    ->color(fn ($state) => match($state) {
                        'en_cours' => 'warning',
                        'cloture' => 'success',
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
                        'en_cours' => 'En cours',
                        'cloture' => 'Clôturé',
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

