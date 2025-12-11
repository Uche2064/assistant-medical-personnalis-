<?php

namespace App\Filament\Resources\Factures\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FacturesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero_facture')
                    ->label('N° Facture')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('prestataire.email')
                    ->label('Prestataire')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('montant_facture')
                    ->label('Montant')
                    ->money('XOF')
                    ->sortable(),

                TextColumn::make('ticket_moderateur')
                    ->label('Ticket modérateur')
                    ->money('XOF')
                    ->sortable(),

                TextColumn::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'en_attente' => 'En attente',
                        'validee_technicien' => 'Validée Technicien',
                        'validee_medecin' => 'Validée Médecin',
                        'autorisee_comptable' => 'Autorisée Comptable',
                        'rembourse' => 'Remboursée',
                        'rejetee_technicien' => 'Rejetée Technicien',
                        'rejetee_medecin' => 'Rejetée Médecin',
                        'rejetee_comptable' => 'Rejetée Comptable',
                        default => ucfirst(str_replace('_', ' ', $state)),
                    })
                    ->color(fn ($state) => match($state) {
                        'en_attente' => 'warning',
                        'validee_technicien', 'validee_medecin' => 'info',
                        'autorisee_comptable' => 'success',
                        'rembourse' => 'success',
                        'rejetee_technicien', 'rejetee_medecin', 'rejetee_comptable' => 'danger',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),

                TextColumn::make('technicien.user.email')
                    ->label('Technicien')
                    ->toggleable(),

                TextColumn::make('medecin.user.email')
                    ->label('Médecin')
                    ->toggleable(),

                TextColumn::make('comptable.user.email')
                    ->label('Comptable')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('statut')
                    ->label('Statut')
                    ->options([
                        'en_attente' => 'En attente',
                        'validee_technicien' => 'Validée Technicien',
                        'validee_medecin' => 'Validée Médecin',
                        'autorisee_comptable' => 'Autorisée Comptable',
                        'rembourse' => 'Remboursée',
                        'rejetee_technicien' => 'Rejetée Technicien',
                        'rejetee_medecin' => 'Rejetée Médecin',
                        'rejetee_comptable' => 'Rejetée Comptable',
                    ])
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
