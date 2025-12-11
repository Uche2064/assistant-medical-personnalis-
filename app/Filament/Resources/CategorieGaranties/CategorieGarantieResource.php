<?php

namespace App\Filament\Resources\CategorieGaranties;

use App\Enums\RoleEnum;
use App\Filament\Resources\CategorieGaranties\Pages\ListCategorieGaranties;
use App\Filament\Resources\CategorieGaranties\Pages\ViewCategorieGarantie;
use App\Filament\Resources\CategorieGaranties\Schemas\CategorieGarantieForm;
use App\Filament\Resources\CategorieGaranties\Schemas\CategorieGarantieInfolist;
use App\Filament\Resources\CategorieGaranties\Tables\CategorieGarantiesTable;
use App\Models\CategorieGarantie;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class CategorieGarantieResource extends Resource
{
    protected static ?string $model = CategorieGarantie::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFolder;

    protected static ?string $recordTitleAttribute = 'libelle';

    protected static ?string $navigationLabel = 'Catégories de garanties';

    protected static ?string $modelLabel = 'Catégorie de garantie';

    protected static ?string $pluralModelLabel = 'Catégories de garanties';

    public static function getNavigationGroup(): ?string
    {
        return 'Contrats et Garanties';
    }

    protected static ?int $navigationSort = 1;

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
        return CategorieGarantieForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CategorieGarantieInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CategorieGarantiesTable::configure($table);
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
            'index' => ListCategorieGaranties::route('/'),
            'create' => \App\Filament\Resources\CategorieGaranties\Pages\CreateCategorieGarantie::route('/create'),
            'view' => ViewCategorieGarantie::route('/{record}'),
            'edit' => \App\Filament\Resources\CategorieGaranties\Pages\EditCategorieGarantie::route('/{record}/edit'),
        ];
    }
}

