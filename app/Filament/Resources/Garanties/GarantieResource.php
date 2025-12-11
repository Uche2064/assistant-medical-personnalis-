<?php

namespace App\Filament\Resources\Garanties;

use App\Enums\RoleEnum;
use App\Filament\Resources\Garanties\Pages\ListGaranties;
use App\Filament\Resources\Garanties\Pages\ViewGarantie;
use App\Filament\Resources\Garanties\Schemas\GarantieForm;
use App\Filament\Resources\Garanties\Schemas\GarantieInfolist;
use App\Filament\Resources\Garanties\Tables\GarantiesTable;
use App\Models\Garantie;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class GarantieResource extends Resource
{
    protected static ?string $model = Garantie::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?string $recordTitleAttribute = 'libelle';

    protected static ?string $navigationLabel = 'Garanties';

    protected static ?string $modelLabel = 'Garantie';

    protected static ?string $pluralModelLabel = 'Garanties';

    public static function getNavigationGroup(): ?string
    {
        return 'Contrats et Garanties';
    }

    protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        return $user && (
            $user->hasRole(RoleEnum::TECHNICIEN->value) ||
            $user->hasRole(RoleEnum::MEDECIN_CONTROLEUR->value)
        );
    }

    public static function canCreate(): bool
    {
        $user = Auth::user();
        return $user && (
            $user->hasRole(\App\Enums\RoleEnum::TECHNICIEN->value) ||
            $user->hasRole(\App\Enums\RoleEnum::MEDECIN_CONTROLEUR->value)
        );
    }

    public static function canEdit($record): bool
    {
        $user = Auth::user();
        return $user && (
            $user->hasRole(\App\Enums\RoleEnum::TECHNICIEN->value) ||
            $user->hasRole(\App\Enums\RoleEnum::MEDECIN_CONTROLEUR->value)
        );
    }

    public static function canDelete($record): bool
    {
        $user = Auth::user();
        return $user && (
            $user->hasRole(\App\Enums\RoleEnum::TECHNICIEN->value) ||
            $user->hasRole(\App\Enums\RoleEnum::MEDECIN_CONTROLEUR->value)
        );
    }

    public static function form(Schema $schema): Schema
    {
        return GarantieForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return GarantieInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GarantiesTable::configure($table);
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
            'index' => ListGaranties::route('/'),
            'create' => \App\Filament\Resources\Garanties\Pages\CreateGarantie::route('/create'),
            'view' => ViewGarantie::route('/{record}'),
            'edit' => \App\Filament\Resources\Garanties\Pages\EditGarantie::route('/{record}/edit'),
        ];
    }
}

