<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('email')
                    ->label('Email address'),
                TextEntry::make('contact')
                    ->placeholder('-'),
                TextEntry::make('adresse')
                    ->placeholder('-'),
                TextEntry::make('photo_url')
                    ->placeholder('-'),
                IconEntry::make('est_actif')
                    ->boolean(),
                TextEntry::make('solde')
                    ->numeric(),
                TextEntry::make('email_verifier_a')
                    ->dateTime()
                    ->placeholder('-'),
                IconEntry::make('mot_de_passe_a_changer')
                    ->boolean(),
                TextEntry::make('personne_id')
                    ->numeric(),
                TextEntry::make('failed_attempts')
                    ->numeric(),
                TextEntry::make('lock_until')
                    ->dateTime()
                    ->placeholder('-'),
                IconEntry::make('permanently_blocked')
                    ->boolean(),
                TextEntry::make('phase')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
