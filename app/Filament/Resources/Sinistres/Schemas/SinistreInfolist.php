<?php

namespace App\Filament\Resources\Sinistres\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SinistreInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('description')
                    ->label('Description')
                    ->columnSpanFull(),

                TextEntry::make('assure.user.email')
                    ->label('Assuré'),

                TextEntry::make('prestataire.user.email')
                    ->label('Prestataire'),

                TextEntry::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'en_cours' => 'En cours',
                        'cloture' => 'Clôturé',
                        default => ucfirst($state),
                    })
                    ->color(fn ($state) => match($state) {
                        'en_cours' => 'warning',
                        'cloture' => 'success',
                        default => 'gray',
                    }),

                TextEntry::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->columns(2);
    }
}

