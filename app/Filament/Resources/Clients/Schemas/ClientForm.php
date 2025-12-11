<?php

namespace App\Filament\Resources\Clients\Schemas;

use App\Enums\ClientTypeEnum;
use App\Enums\SexeEnum;
use App\Models\CommercialParrainageCode;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Code parrainage (readonly, récupéré automatiquement)
                TextInput::make('code_parrainage_display')
                    ->label('Code Parrainage')
                    ->disabled()
                    ->dehydrated(false)
                    ->default(function () {
                        $commercial = Auth::user();
                        if ($commercial) {
                            $currentCode = CommercialParrainageCode::getCurrentCode($commercial->id);
                            return $currentCode ? $currentCode->code_parrainage : 'Aucun code actif';
                        }
                        return 'Aucun code';
                    })
                    ->helperText('Code parrainage automatiquement associé à votre compte')
                    ->columnSpanFull(),

                // Type de client
                Select::make('type_client')
                    ->label('Type de Client')
                    ->options(function () {
                        $options = [];
                        foreach (ClientTypeEnum::cases() as $type) {
                            $options[$type->value] = $type->getLabel();
                        }
                        return $options;
                    })
                    ->required()
                    ->native(false)
                    ->live()
                    ->afterStateUpdated(fn ($state, $set) => $set('prenoms', null)),

                // Nom (raison sociale pour moral, nom pour physique)
                TextInput::make('nom')
                    ->label(fn ($get) => $get('type_client') === ClientTypeEnum::MORAL->value ? 'Raison Sociale' : 'Nom')
                    ->required()
                    ->maxLength(255)
                    ->visible(fn ($get) => $get('type_client') !== null),

                // Prénoms (uniquement pour physique)
                TextInput::make('prenoms')
                    ->label('Prénoms')
                    ->required()
                    ->maxLength(255)
                    ->visible(fn ($get) => $get('type_client') === ClientTypeEnum::PHYSIQUE->value),

                // Date de naissance (uniquement pour physique)
                DatePicker::make('date_naissance')
                    ->label('Date de naissance')
                    ->required()
                    ->displayFormat('d/m/Y')
                    ->maxDate(now()->subDay())
                    ->visible(fn ($get) => $get('type_client') === ClientTypeEnum::PHYSIQUE->value),

                // Sexe (uniquement pour physique)
                Select::make('sexe')
                    ->label('Sexe')
                    ->options(SexeEnum::options())
                    ->required()
                    ->native(false)
                    ->visible(fn ($get) => $get('type_client') === ClientTypeEnum::PHYSIQUE->value),

                // Profession (uniquement pour physique, optionnel)
                TextInput::make('profession')
                    ->label('Profession')
                    ->maxLength(255)
                    ->visible(fn ($get) => $get('type_client') === ClientTypeEnum::PHYSIQUE->value),

                // Email (commun)
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(table: 'users', column: 'email', ignoreRecord: true)
                    ->visible(fn ($get) => $get('type_client') !== null),

                // Contact (commun)
                TextInput::make('contact')
                    ->label('Numéro de téléphone')
                    ->required()
                    ->maxLength(30)
                    ->unique(table: 'users', column: 'contact', ignoreRecord: true)
                    ->visible(fn ($get) => $get('type_client') !== null),

                // Adresse (commun)
                Textarea::make('adresse')
                    ->label('Adresse')
                    ->required()
                    ->rows(3)
                    ->maxLength(500)
                    ->visible(fn ($get) => $get('type_client') !== null),

                // Photo (uniquement pour physique)
                FileUpload::make('photo')
                    ->label('Photo')
                    ->image()
                    ->directory('uploads')
                    ->visibility('public')
                    ->maxSize(2048)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg'])
                    ->required()
                    ->visible(fn ($get) => $get('type_client') === ClientTypeEnum::PHYSIQUE->value),
            ]);
    }
}
