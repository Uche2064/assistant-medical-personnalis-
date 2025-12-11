<?php

namespace App\Filament\Resources\Gestionnaires\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class GestionnaireInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('user.email')
                    ->label('Email'),
                TextEntry::make('user.contact')
                    ->label('Contact'),
                TextEntry::make('user.adresse')
                    ->label('Adresse'),
                ImageEntry::make('user.photo_url')
                    ->label('Photo')
                    ->disk('public')
                    ->circular()
                    ->columnSpanFull()
                    ->defaultImageUrl(url('/images/default-avatar.png'))
                    ->visibility('public'),
                TextEntry::make('user.personne.nom')
                    ->label('Nom'),
                TextEntry::make('user.personne.prenoms')
                    ->label('Prénoms'),
                TextEntry::make('user.personne.date_naissance')
                    ->label('Date de naissance')
                    ->date('d/m/Y'),
                TextEntry::make('user.personne.sexe')
                    ->label('Sexe')
                    ->formatStateUsing(fn ($state) => $state === 'M' ? 'Masculin' : 'Féminin'),
                TextEntry::make('user.personne.profession')
                    ->label('Profession'),
            ])
            ->columns(2);
    }
}

