<?php

namespace App\Filament\Resources\Garanties\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class GarantieForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('libelle')
                    ->label('Libellé')
                    ->required()
                    ->maxLength(255),

                Select::make('categorie_garantie_id')
                    ->label('Catégorie')
                    ->relationship('categorieGarantie', 'libelle')
                    ->required()
                    ->searchable()
                    ->preload(),

                TextInput::make('plafond')
                    ->label('Plafond')
                    ->numeric()
                    ->prefix('FCFA')
                    ->required()
                    ->step(0.01),

                TextInput::make('prix_standard')
                    ->label('Prix standard')
                    ->numeric()
                    ->prefix('FCFA')
                    ->required()
                    ->step(0.01),

                TextInput::make('taux_couverture')
                    ->label('Taux couverture (%)')
                    ->numeric()
                    ->suffix('%')
                    ->required()
                    ->minValue(0)
                    ->maxValue(100)
                    ->step(0.01),

                Toggle::make('est_active')
                    ->label('Actif')
                    ->default(true),
            ]);
    }
}

