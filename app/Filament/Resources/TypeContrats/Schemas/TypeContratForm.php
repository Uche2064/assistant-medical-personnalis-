<?php

namespace App\Filament\Resources\TypeContrats\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TypeContratForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('libelle')
                    ->label('Libellé')
                    ->required()
                    ->maxLength(255),

                TextInput::make('prime_standard')
                    ->label('Prime standard')
                    ->numeric()
                    ->prefix('FCFA')
                    ->required()
                    ->step(0.01),

                Select::make('technicien_id')
                    ->label('Technicien')
                    ->options(function () {
                        return \App\Models\Personnel::with('user')
                            ->whereHas('user.roles', function ($query) {
                                $query->where('name', \App\Enums\RoleEnum::TECHNICIEN->value);
                            })
                            ->get()
                            ->mapWithKeys(function ($personnel) {
                                return [$personnel->id => $personnel->user->email ?? 'N/A'];
                            })
                            ->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->default(function () {
                        $user = \Illuminate\Support\Facades\Auth::user();
                        if ($user && $user->hasRole(\App\Enums\RoleEnum::TECHNICIEN->value) && $user->personnel) {
                            return $user->personnel->id;
                        }
                        return null;
                    })
                    ->disabled(fn () => \Illuminate\Support\Facades\Auth::user()?->hasRole(\App\Enums\RoleEnum::TECHNICIEN->value)),

                Toggle::make('est_actif')
                    ->label('Actif')
                    ->default(true),
            ]);
    }
}

