<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\RoleEnum;
use App\Models\Personne;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                TextInput::make('contact')
                    ->label('Contact')
                    ->tel()
                    ->maxLength(255),

                TextInput::make('password')
                    ->label('Mot de passe')
                    ->password()
                    ->required(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord)
                    ->dehydrated(fn ($state) => filled($state))
                    ->dehydrateStateUsing(fn ($state) => \Hash::make($state))
                    ->minLength(8),

                TextInput::make('adresse')
                    ->label('Adresse')
                    ->maxLength(255),

                TextInput::make('photo_url')
                    ->label('URL Photo')
                    ->url()
                    ->maxLength(255),

                Select::make('personne_id')
                    ->label('Personne')
                    ->relationship('personne', 'nom', fn ($query) => $query->orderBy('nom'))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->createOptionForm([
                        TextInput::make('nom')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('prenoms')
                            ->required()
                            ->maxLength(255),
                        DatePicker::make('date_naissance')
                            ->label('Date de naissance')
                            ->displayFormat('d/m/Y'),
                        Select::make('sexe')
                            ->options([
                                'M' => 'Masculin',
                                'F' => 'Féminin',
                            ]),
                        TextInput::make('profession')
                            ->maxLength(255),
                    ]),

                Select::make('roles')
                    ->label('Rôles')
                    ->multiple()
                    ->relationship('roles', 'name')
                    ->preload()
                    ->searchable()
                    ->options(function () {
                        return Role::pluck('name', 'name');
                    }),

                Toggle::make('est_actif')
                    ->label('Actif')
                    ->default(true)
                    ->required(),

                TextInput::make('solde')
                    ->label('Solde')
                    ->numeric()
                    ->default(0)
                    ->prefix('FCFA'),

                DateTimePicker::make('email_verifier_a')
                    ->label('Email vérifié le')
                    ->displayFormat('d/m/Y H:i'),

                Toggle::make('mot_de_passe_a_changer')
                    ->label('Mot de passe à changer')
                    ->default(false),

                TextInput::make('failed_attempts')
                    ->label('Tentatives échouées')
                    ->numeric()
                    ->default(0)
                    ->disabled(),

                DateTimePicker::make('lock_until')
                    ->label('Bloqué jusqu\'au')
                    ->displayFormat('d/m/Y H:i'),

                Toggle::make('permanently_blocked')
                    ->label('Bloqué définitivement')
                    ->default(false),

                TextInput::make('phase')
                    ->label('Phase')
                    ->numeric()
                    ->default(1)
                    ->disabled(),
            ]);
    }
}
