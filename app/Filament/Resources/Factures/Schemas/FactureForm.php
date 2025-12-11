<?php

namespace App\Filament\Resources\Factures\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class FactureForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('diagnostic')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('numero_facture')
                    ->required(),
                TextInput::make('sinistre_id')
                    ->required()
                    ->numeric(),
                TextInput::make('prestataire_id')
                    ->required()
                    ->numeric(),
                TextInput::make('montant_facture')
                    ->required()
                    ->numeric(),
                TextInput::make('ticket_moderateur')
                    ->required()
                    ->numeric(),
                TextInput::make('statut')
                    ->required()
                    ->default('en_attente'),
                Textarea::make('motif_rejet')
                    ->columnSpanFull(),
                TextInput::make('technicien_id')
                    ->numeric(),
                DateTimePicker::make('valide_par_technicien_a'),
                TextInput::make('medecin_id')
                    ->numeric(),
                DateTimePicker::make('valide_par_medecin_a'),
                TextInput::make('comptable_id')
                    ->numeric(),
                DateTimePicker::make('autorise_par_comptable_a'),
            ]);
    }
}
