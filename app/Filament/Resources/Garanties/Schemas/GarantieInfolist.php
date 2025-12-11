<?php

namespace App\Filament\Resources\Garanties\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class GarantieInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('libelle')
                    ->label('Libellé'),

                TextEntry::make('categorieGarantie.libelle')
                    ->label('Catégorie'),

                TextEntry::make('plafond')
                    ->label('Plafond')
                    ->money('XOF'),

                TextEntry::make('prix_standard')
                    ->label('Prix standard')
                    ->money('XOF'),

                TextEntry::make('taux_couverture')
                    ->label('Taux couverture')
                    ->formatStateUsing(fn ($state) => $state . '%'),

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

