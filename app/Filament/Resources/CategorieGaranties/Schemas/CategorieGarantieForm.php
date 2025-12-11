<?php

namespace App\Filament\Resources\CategorieGaranties\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CategorieGarantieForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('libelle')
                    ->label('Libellé')
                    ->required()
                    ->maxLength(255),

                Textarea::make('description')
                    ->label('Description')
                    ->maxLength(65535)
                    ->columnSpanFull(),

                Toggle::make('est_active')
                    ->label('Actif')
                    ->default(true),
            ]);
    }
}

