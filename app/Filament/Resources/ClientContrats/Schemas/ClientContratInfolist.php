<?php

namespace App\Filament\Resources\ClientContrats\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ClientContratInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('numero_police')
                    ->label('Numéro de police'),

                TextEntry::make('client.user.email')
                    ->label('Client'),

                TextEntry::make('typeContrat.libelle')
                    ->label('Type de contrat'),

                TextEntry::make('date_debut')
                    ->label('Date de début')
                    ->date('d/m/Y'),

                TextEntry::make('date_fin')
                    ->label('Date de fin')
                    ->date('d/m/Y'),

                TextEntry::make('statut')
                    ->label('Statut')
                    ->getStateUsing(fn ($record) => $record->statut->getLabel())
                    ->badge()
                    ->color(fn ($record) => $record->statut->getColor()),

                TextEntry::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->columns(2);
    }
}

