<?php

namespace App\Filament\Resources\Gestionnaires\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class GestionnaireForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nom')
                    ->label('Nom')
                    ->required()
                    ->maxLength(255),
                
                TextInput::make('prenoms')
                    ->label('Prénoms')
                    ->maxLength(255),
                
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->unique('users', 'email')
                    ->maxLength(255),
                
                TextInput::make('contact')
                    ->label('Contact')
                    ->tel()
                    ->required()
                    ->unique('users', 'contact')
                    ->maxLength(255),
                
                TextInput::make('adresse')
                    ->label('Adresse')
                    ->maxLength(255),
                
                FileUpload::make('photo')
                    ->label('Photo')
                    ->image()
                    ->directory('uploads')
                    ->disk('public')
                    ->visibility('public')
                    ->maxSize(2048) // 2MB en KB (limite PHP)
                    ->helperText('Taille maximale : 2MB. Formats acceptés : JPEG, PNG, GIF, WEBP')
                    ->columnSpanFull(),
                
                DatePicker::make('date_naissance')
                    ->label('Date de naissance')
                    ->displayFormat('d/m/Y'),
                
                Select::make('sexe')
                    ->label('Sexe')
                    ->options([
                        'M' => 'Masculin',
                        'F' => 'Féminin',
                    ]),
                
                TextInput::make('profession')
                    ->label('Profession')
                    ->maxLength(255),
            ]);
    }
}
