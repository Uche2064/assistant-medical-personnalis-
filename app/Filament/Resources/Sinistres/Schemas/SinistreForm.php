<?php

namespace App\Filament\Resources\Sinistres\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SinistreForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('assure_id')
                    ->label('Assuré')
                    ->relationship('assure', 'user.email')
                    ->required()
                    ->searchable()
                    ->preload(),

                Select::make('prestataire_id')
                    ->label('Prestataire')
                    ->relationship('prestataire', 'user.email')
                    ->required()
                    ->searchable()
                    ->preload(),

                Textarea::make('description')
                    ->label('Description')
                    ->required()
                    ->maxLength(65535)
                    ->columnSpanFull(),

                Select::make('statut')
                    ->label('Statut')
                    ->options([
                        'en_cours' => 'En cours',
                        'cloture' => 'Clôturé',
                    ])
                    ->required()
                    ->default('en_cours'),
            ]);
    }
}

