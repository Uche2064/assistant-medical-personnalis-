<?php

namespace App\Filament\Widgets;

use App\Enums\RoleEnum;
use App\Models\Personnel;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class DerniersPersonnelsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = '5 derniers personnels ajoutés';

    public function table(Table $table): Table
    {
        $user = Auth::user();
        $isAdminGlobal = $user->hasRole(RoleEnum::ADMIN_GLOBAL->value);

        return $table
            ->query(function (): Builder {
                $user = Auth::user();
                $isGestionnaire = $user->hasRole(RoleEnum::GESTIONNAIRE->value);

                $query = Personnel::query()
                    ->with(['user.roles', 'user.personne'])
                    ->whereHas('user', function ($userQuery) {
                        $userQuery->whereHas('roles', function ($roleQuery) {
                            $roleQuery->whereNotIn('name', [RoleEnum::ADMIN_GLOBAL->value]);
                        });
                    });

                // Si gestionnaire, filtrer par gestionnaire_id
                // et exclure le gestionnaire connecté lui-même
                if ($isGestionnaire && $user->personnel) {
                    $query->where('gestionnaire_id', $user->personnel->id)
                          ->where('id', '!=', $user->personnel->id);
                }

                return $query->orderBy('created_at', 'desc')->limit(5);
            })
            ->columns([
                TextColumn::make('user.personne.nom')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.personne.prenoms')
                    ->label('Prénoms')
                    ->searchable(),

                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.roles.name')
                    ->label('Rôle')
                    ->badge()
                    ->separator(','),

                TextColumn::make('user.est_actif')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Actif' : 'Inactif')
                    ->color(fn ($state) => $state ? 'success' : 'danger'),

                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
