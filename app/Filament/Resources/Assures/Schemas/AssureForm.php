<?php

namespace App\Filament\Resources\Assures\Schemas;

use App\Models\Client;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AssureForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('Utilisateur')
                    ->relationship('user', 'email')
                    ->searchable()
                    ->preload()
                    ->required(),
                
                Select::make('client_id')
                    ->label('Client')
                    ->relationship('client', 'user.email')
                    ->searchable()
                    ->preload()
                    ->required(),
                
                Toggle::make('est_principal')
                    ->label('Assuré principal')
                    ->default(false)
                    ->required(),
                
                Select::make('lien_parente')
                    ->label('Lien parenté')
                    ->options(\App\Enums\LienParenteEnum::class)
                    ->visible(fn ($get) => !$get('est_principal')),
                
                Select::make('assure_principal_id')
                    ->label('Assuré principal')
                    ->relationship('assurePrincipal', 'user.email')
                    ->searchable()
                    ->preload()
                    ->visible(fn ($get) => !$get('est_principal')),
            ]);
    }
}

