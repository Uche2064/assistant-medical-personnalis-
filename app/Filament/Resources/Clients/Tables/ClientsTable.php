<?php

namespace App\Filament\Resources\Clients\Tables;

use App\Enums\ClientTypeEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Facades\Filament;
use App\Enums\RoleEnum;


class ClientsTable
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

                TextColumn::make('type_client')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->getLabel() ?? 'N/A')
                    ->color(fn ($state) => match ($state?->value ?? '') {
                        'physique' => 'info',
                        'moral' => 'success',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),

                TextColumn::make('assures_count')
                    ->label('Nb Assurés')
                    ->counts('assures')
                    ->sortable(),

                IconColumn::make('user.est_actif')
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
                SelectFilter::make('type_client')
                    ->label('Type client')
                    ->options(collect(ClientTypeEnum::cases())->mapWithKeys(fn ($case) => [$case->value => $case->getLabel()])->toArray())
                    ->multiple()
                    ->preload(),
                SelectFilter::make('user.est_actif')
                    ->label('Statut')
                    ->options([
                        1 => 'Actif',
                        0 => 'Inactif',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(function ($record) {
                        $user = Filament::auth()->user() ?? Auth::user();
                        if (! $user) {
                            return false;
                        }

                        // Les rôles qui ne peuvent pas modifier
                        return ! $user->hasAnyRole([
                            RoleEnum::TECHNICIEN->value,
                            RoleEnum::MEDECIN_CONTROLEUR->value,
                            RoleEnum::ADMIN_GLOBAL->value,
                            RoleEnum::GESTIONNAIRE->value,
                            RoleEnum::COMPTABLE->value,
                            RoleEnum::COMMERCIAL->value,
                        ]);
                    }),            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
