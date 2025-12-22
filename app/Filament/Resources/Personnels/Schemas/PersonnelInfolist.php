<?php

namespace App\Filament\Resources\Personnels\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PersonnelInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.personne.nom')
                    ->label('Nom'),
                TextEntry::make('user.personne.prenoms')
                    ->label('Prénoms'),
                TextEntry::make('user.email')
                    ->label('Email'),
                TextEntry::make('user.contact')
                    ->label('Contact'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('user_id')
                    ->numeric(),
                TextEntry::make('gestionnaire_id')
                    ->numeric()
                    ->placeholder('-'),
                ImageEntry::make('user.photo_url')
                    ->label('Photo')
                    ->disk('public')
                    ->circular()
                    ->placeholder('-'),
            ]);
    }
}
