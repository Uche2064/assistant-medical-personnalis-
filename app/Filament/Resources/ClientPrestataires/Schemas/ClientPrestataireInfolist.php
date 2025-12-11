<?php

namespace App\Filament\Resources\ClientPrestataires\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ClientPrestataireInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('clientContrat.client.user.email')
                    ->label('Client'),

                TextEntry::make('prestataire.user.email')
                    ->label('Prestataire'),

                TextEntry::make('type_prestataire')
                    ->label('Type prestataire')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'pharmacie' => 'Pharmacie',
                        'centre_soins' => 'Centre de Soins',
                        'optique' => 'Optique',
                        'laboratoire_centre_diagnostic' => 'Laboratoire et centre de diagnostic',
                        default => ucfirst($state),
                    })
                    ->color('info'),

                TextEntry::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match($state) {
                        'actif' => 'Actif',
                        'inactif' => 'Inactif',
                        default => ucfirst($state),
                    })
                    ->color(fn ($state) => match($state) {
                        'actif' => 'success',
                        'inactif' => 'danger',
                        default => 'gray',
                    }),

                TextEntry::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->columns(2);
    }
}

