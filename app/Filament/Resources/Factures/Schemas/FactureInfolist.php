<?php

namespace App\Filament\Resources\Factures\Schemas;

use App\Models\Facture;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class FactureInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('diagnostic')
                    ->columnSpanFull(),
                TextEntry::make('numero_facture'),
                TextEntry::make('sinistre_id')
                    ->numeric(),
                TextEntry::make('prestataire_id')
                    ->numeric(),
                TextEntry::make('montant_facture')
                    ->numeric(),
                TextEntry::make('ticket_moderateur')
                    ->numeric(),
                TextEntry::make('statut'),
                TextEntry::make('motif_rejet')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('technicien_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('valide_par_technicien_a')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('medecin_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('valide_par_medecin_a')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('comptable_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('autorise_par_comptable_a')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Facture $record): bool => $record->trashed()),
            ]);
    }
}
