<?php

namespace App\Filament\Resources\DemandeAdhesions\Schemas;

use App\Enums\StatutDemandeAdhesionEnum;
use App\Enums\TypeDemandeurEnum;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class DemandeAdhesionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type_demandeur')
                    ->options(TypeDemandeurEnum::class)
                    ->required(),
                Select::make('statut')
                    ->options(StatutDemandeAdhesionEnum::class)
                    ->default('en_attente')
                    ->required(),
                Textarea::make('motif_rejet')
                    ->columnSpanFull(),
                TextInput::make('valide_par_id')
                    ->numeric(),
                DateTimePicker::make('valider_a'),
                TextInput::make('user_id')
                    ->numeric(),
            ]);
    }
}
