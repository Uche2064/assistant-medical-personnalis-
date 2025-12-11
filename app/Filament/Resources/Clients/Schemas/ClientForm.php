<?php

namespace App\Filament\Resources\Clients\Schemas;

use App\Enums\ClientTypeEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                TextInput::make('code_parrainage')
                    ->required(),
                Select::make('type_client')
                    ->options(ClientTypeEnum::class)
                    ->required(),
            ]);
    }
}
