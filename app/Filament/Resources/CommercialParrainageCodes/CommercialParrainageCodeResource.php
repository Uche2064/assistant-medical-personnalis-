<?php

namespace App\Filament\Resources\CommercialParrainageCodes;

use App\Enums\RoleEnum;
use App\Filament\Resources\CommercialParrainageCodes\Pages\ListCommercialParrainageCodes;
use App\Filament\Resources\CommercialParrainageCodes\Tables\CommercialParrainageCodesTable;
use App\Models\CommercialParrainageCode;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class CommercialParrainageCodeResource extends Resource
{
    protected static ?string $model = CommercialParrainageCode::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static ?string $navigationLabel = 'Codes Parrainage';

    protected static ?string $modelLabel = 'Code Parrainage';

    protected static ?string $pluralModelLabel = 'Codes Parrainage';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return null;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::check() && Auth::user()->hasRole(RoleEnum::COMMERCIAL->value);
    }

    public static function canCreate(): bool
    {
        return false; // Les codes sont générés automatiquement via l'API
    }

    public static function canEdit($record): bool
    {
        return false; // Les codes ne peuvent pas être modifiés
    }

    public static function canDelete($record): bool
    {
        return false; // Les codes ne peuvent pas être supprimés
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCommercialParrainageCodes::route('/'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function table($table): \Filament\Tables\Table
    {
        return CommercialParrainageCodesTable::make($table);
    }
}

