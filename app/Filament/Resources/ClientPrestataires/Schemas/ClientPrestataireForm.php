<?php

namespace App\Filament\Resources\ClientPrestataires\Schemas;

use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class ClientPrestataireForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('client_contrat_id')
                    ->label('Contrat client')
                    ->relationship('clientContrat', 'numero_police')
                    ->required()
                    ->searchable()
                    ->preload(),

                Select::make('prestataire_id')
                    ->label('Prestataire')
                    ->relationship('prestataire', 'user.email')
                    ->required()
                    ->searchable()
                    ->preload(),

                Select::make('type_prestataire')
                    ->label('Type prestataire')
                    ->options([
                        'pharmacie' => 'Pharmacie',
                        'centre_soins' => 'Centre de Soins',
                        'optique' => 'Optique',
                        'laboratoire_centre_diagnostic' => 'Laboratoire et centre de diagnostic',
                    ])
                    ->required(),

                Select::make('statut')
                    ->label('Statut')
                    ->options([
                        'actif' => 'Actif',
                        'inactif' => 'Inactif',
                    ])
                    ->required()
                    ->default('actif'),
            ]);
    }
}

