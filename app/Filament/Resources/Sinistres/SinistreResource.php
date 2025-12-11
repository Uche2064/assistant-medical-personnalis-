<?php

namespace App\Filament\Resources\Sinistres;

use App\Enums\RoleEnum;
use App\Filament\Resources\Sinistres\Pages\ListSinistres;
use App\Filament\Resources\Sinistres\Pages\ViewSinistre;
use App\Filament\Resources\Sinistres\Schemas\SinistreForm;
use App\Filament\Resources\Sinistres\Schemas\SinistreInfolist;
use App\Filament\Resources\Sinistres\Tables\SinistresTable;
use App\Models\Sinistre;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class SinistreResource extends Resource
{
    protected static ?string $model = Sinistre::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    protected static ?string $recordTitleAttribute = 'description';

    protected static ?string $navigationLabel = 'Sinistres';

    protected static ?string $modelLabel = 'Sinistre';

    protected static ?string $pluralModelLabel = 'Sinistres';

    public static function getNavigationGroup(): ?string
    {
        return 'Gestion';
    }

    protected static ?int $navigationSort = 4;

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        return $user && $user->hasRole(RoleEnum::TECHNICIEN->value);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return SinistreForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SinistreInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SinistresTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSinistres::route('/'),
            'view' => ViewSinistre::route('/{record}'),
        ];
    }
}

