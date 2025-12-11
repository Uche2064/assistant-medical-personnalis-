<?php

namespace App\Filament\Resources\Questions\Schemas;

use App\Enums\ClientTypeEnum;
use App\Enums\TypeDonneeEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class QuestionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('libelle')
                    ->label('Libellé de la question')
                    ->required()
                    ->rows(3)
                    ->maxLength(500),

                Select::make('type_de_donnee')
                    ->label('Type de donnée')
                    ->options(function () {
                        $options = [];
                        foreach (TypeDonneeEnum::cases() as $type) {
                            $options[$type->value] = $type->getLabel();
                        }
                        return $options;
                    })
                    ->required()
                    ->native(false)
                    ->live()
                    ->afterStateUpdated(fn ($state, $set) => $set('options', [])),

                Select::make('destinataire')
                    ->label('Destinataire')
                    ->required()
                    ->options(function () {
                        $options = [];

                        // Ajouter les types de prestataires avec préfixe "Prestataire - "
                        foreach (\App\Enums\TypePrestataireEnum::cases() as $type) {
                            $options[$type->value] = 'Prestataire - ' . $type->getLabel();
                        }

                        // Ajouter les types de clients avec préfixe "Client - "
                        $options[ClientTypeEnum::PHYSIQUE->value] = 'Client - ' . ClientTypeEnum::PHYSIQUE->getLabel();

                        return $options;
                    })
                    ->searchable()
                    ->native(false)
                    ->helperText('Sélectionnez le destinataire de cette question'),

                Repeater::make('options')
                    ->label('Options')
                    ->schema([
                        TextInput::make('option')
                            ->label('Option')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Entrez une option'),
                    ])
                    ->defaultItems(0)
                    ->addActionLabel('Ajouter une option')
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => $state['option'] ?? null)
                    ->visible(fn ($get) => in_array($get('type_de_donnee'), [
                        TypeDonneeEnum::SELECT->value,
                        TypeDonneeEnum::RADIO->value,
                        TypeDonneeEnum::CHECKBOX->value,
                    ]))
                    ->helperText('Ajoutez les options disponibles pour cette question. Ce champ n\'est visible que pour les types SELECT, RADIO et CHECKBOX.')
                    ->reorderableWithButtons()
                    ->deletable()
                    ->minItems(0),

                Toggle::make('est_obligatoire')
                    ->label('Question obligatoire')
                    ->default(false),

                Toggle::make('est_active')
                    ->label('Question active')
                    ->default(true),
            ]);
    }
}

