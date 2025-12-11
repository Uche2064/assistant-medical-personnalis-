<?php

namespace App\Filament\Resources\Assures\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AssureInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations Assuré')
                    ->schema([
                        TextEntry::make('user.email')
                            ->label('Email'),
                        TextEntry::make('user.personne.nom')
                            ->label('Nom'),
                        TextEntry::make('user.personne.prenoms')
                            ->label('Prénoms'),
                        IconEntry::make('est_principal')
                            ->label('Principal')
                            ->boolean(),
                        TextEntry::make('lien_parente')
                            ->label('Lien parenté')
                            ->formatStateUsing(fn ($state) => $state?->getLabel() ?? 'N/A'),
                    ])
                    ->columns(2),
                
                Section::make('Client Associé')
                    ->schema([
                        TextEntry::make('client.user.email')
                            ->label('Email client'),
                    ]),
            ]);
    }
}

