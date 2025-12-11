<?php

namespace App\Filament\Resources\DemandeAdhesions\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class DemandeAdhesionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('type_demandeur')
                    ->badge(),
                TextEntry::make('statut')
                    ->badge(),
                TextEntry::make('motif_rejet')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('valide_par_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('valider_a')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('user_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
