<?php

namespace App\Filament\Resources\Personnels\Schemas;

use App\Enums\RoleEnum;
use App\Enums\SexeEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Filament\Facades\Filament;

class PersonnelForm
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
                    ->unique('users', 'email', ignoreRecord: true)
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->debounce(500),

                TextInput::make('contact')
                    ->label('Numéro de téléphone')
                    ->tel()
                    ->required()
                    ->unique('users', 'contact', ignoreRecord: true)
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->debounce(500),
                Select::make('sexe')
                    ->label('Sexe')
                    ->options(SexeEnum::options())
                    ->required(),

                FileUpload::make('photo')
                    ->label('Photo')
                    ->image()
                    ->directory('uploads')
                    ->disk('public')
                    ->visibility('public')
                    ->maxSize(2048) // 2MB en KB (limite PHP)
                    ->helperText('Taille maximale : 2MB. Formats acceptés : JPEG, PNG, GIF, WEBP'),

                DatePicker::make('date_naissance')
                    ->label('Date de naissance')
                    ->displayFormat('d/m/Y'),

                Select::make('role')
                    ->label('Rôle')
                    ->required()
                    ->options(function () {
                        $user = Auth::user() ?? Filament::auth()->user() ;
                        $options = [];

                        if ($user && $user->hasRole(RoleEnum::ADMIN_GLOBAL->value)) {
                            // Admin global peut seulement créer des gestionnaires
                            $options[RoleEnum::GESTIONNAIRE->value] = RoleEnum::getLabel(RoleEnum::GESTIONNAIRE->value);
                        } elseif ($user && $user->hasRole(RoleEnum::GESTIONNAIRE->value)) {
                            // Gestionnaire peut créer : technicien, comptable, commercial, medecin_controleur
                            $allowedRoles = [
                                RoleEnum::TECHNICIEN->value,
                                RoleEnum::COMPTABLE->value,
                                RoleEnum::COMMERCIAL->value,
                                RoleEnum::MEDECIN_CONTROLEUR->value,
                            ];

                            foreach ($allowedRoles as $roleValue) {
                                $options[$roleValue] = RoleEnum::getLabel($roleValue);
                            }
                        }

                        return $options;
                    })
                    ->searchable(),
            ]);
    }
}
