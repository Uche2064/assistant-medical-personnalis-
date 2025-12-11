<?php

namespace App\Filament\Resources\DemandeAdhesions\Tables;

use App\Enums\RoleEnum;
use App\Enums\StatutDemandeAdhesionEnum;
use App\Enums\TypeDemandeurEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class DemandeAdhesionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.email')
                    ->label('Utilisateur')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type_demandeur')
                    ->label('Type demandeur')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->getLabel() ?? $state)
                    ->color(fn ($state) => match($state?->value ?? '') {
                        'client' => 'success',
                        'prestataire' => 'info',
                        default => 'gray',
                    })
                    ->searchable(),

                TextColumn::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->getLabel() ?? $state)
                    ->color(fn ($state) => match($state?->value ?? '') {
                        'en_attente' => 'warning',
                        'validee' => 'success',
                        'rejetee' => 'danger',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),

                TextColumn::make('validePar.user.email')
                    ->label('Validé par')
                    ->searchable(),

                TextColumn::make('valider_a')
                    ->label('Validé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('motif_rejet')
                    ->label('Motif rejet')
                    ->limit(50)
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
                    ->options(collect(StatutDemandeAdhesionEnum::cases())->mapWithKeys(fn ($case) => [$case->value => $case->getLabel()])->toArray())
                    ->multiple()
                    ->preload(),
                SelectFilter::make('type_demandeur')
                    ->label('Type demandeur')
                    ->options(collect(TypeDemandeurEnum::cases())->mapWithKeys(fn ($case) => [$case->value => $case->getLabel()])->toArray())
                    ->multiple()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(function ($record) {
                        $user = Filament::auth()->user() ?? Auth::user();
                        if (!$user) {
                            return false;
                        }
                        // Les techniciens et médecins contrôleurs ne peuvent pas modifier
                        return !$user->hasAnyRole([
                            RoleEnum::TECHNICIEN->value,
                            RoleEnum::MEDECIN_CONTROLEUR->value,
                        ]);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
