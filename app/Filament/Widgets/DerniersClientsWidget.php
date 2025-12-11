<?php

namespace App\Filament\Widgets;

use App\Enums\RoleEnum;
use App\Models\User;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class DerniersClientsWidget extends TableWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        $commercial = Auth::user();

        if (!$commercial) {
            return $table
                ->query(fn (): Builder => User::query()->whereRaw('1 = 0'))
                ->columns([]);
        }

        return $table
            ->query(
                fn (): Builder => User::query()
                    ->where('personne_id', $commercial->id)
                    ->whereHas('roles', fn($q) => $q->where('name', RoleEnum::CLIENT->value))
                    ->with(['personne', 'client'])
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('personne.nom')
                    ->label('Nom')
                    ->searchable(),

                TextColumn::make('personne.prenoms')
                    ->label('Prénoms')
                    ->searchable(),

                TextColumn::make('client.type_client')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->getLabel() ?? 'N/A')
                    ->color(fn ($state) => match($state?->value ?? '') {
                        'physique' => 'info',
                        'moral' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Date d\'ajout')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('est_actif')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Actif' : 'Inactif')
                    ->color(fn ($state) => $state ? 'success' : 'danger'),
            ])
            ->heading('5 Derniers Clients Ajoutés')
            ->defaultSort('created_at', 'desc');
    }
}
