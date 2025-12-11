<?php

namespace App\Filament\Resources\ClientContrats\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ClientContratForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('numero_police')
                    ->label('Numéro de police')
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                Select::make('client_id')
                    ->label('Client')
                    ->relationship('client', 'user.email')
                    ->required()
                    ->searchable()
                    ->preload(),

                Select::make('type_contrat_id')
                    ->label('Type de contrat')
                    ->relationship('typeContrat', 'libelle')
                    ->required()
                    ->searchable()
                    ->preload(),

                DatePicker::make('date_debut')
                    ->label('Date de début')
                    ->required()
                    ->displayFormat('d/m/Y'),

                DatePicker::make('date_fin')
                    ->label('Date de fin')
                    ->required()
                    ->displayFormat('d/m/Y')
                    ->after('date_debut'),

                Select::make('statut')
                    ->label('Statut')
                    ->options(function () {
                        $options = [];
                        foreach (\App\Enums\StatutContratEnum::cases() as $statut) {
                            $options[$statut->value] = $statut->getLabel();
                        }
                        return $options;
                    })
                    ->required()
                    ->native(false),
            ]);
    }
}

