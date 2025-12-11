<?php

namespace App\Filament\Resources\TypeContrats\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TypeContratInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('libelle')
                    ->label('Libellé'),

                TextEntry::make('prime_standard')
                    ->label('Prime standard')
                    ->money('XOF'),

                TextEntry::make('technicien.user.email')
                    ->label('Technicien'),

                IconEntry::make('est_actif')
                    ->label('Actif')
                    ->boolean(),

                TextEntry::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->columns(2);
    }
}

