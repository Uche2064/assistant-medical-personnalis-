<?php

namespace App\Filament\Resources\CategorieGaranties\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CategorieGarantieInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('libelle')
                    ->label('Libellé'),

                TextEntry::make('description')
                    ->label('Description')
                    ->columnSpanFull(),

                TextEntry::make('garanties_count')
                    ->label('Nombre de garanties')
                    ->default(0),

                IconEntry::make('est_active')
                    ->label('Actif')
                    ->boolean(),

                TextEntry::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->columns(2);
    }
}

