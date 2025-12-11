<?php

namespace App\Filament\Resources\TypeContrats;

use App\Enums\RoleEnum;
use App\Filament\Resources\TypeContrats\Pages\ListTypeContrats;
use App\Filament\Resources\TypeContrats\Pages\ViewTypeContrat;
use App\Filament\Resources\TypeContrats\Schemas\TypeContratForm;
use App\Filament\Resources\TypeContrats\Schemas\TypeContratInfolist;
use App\Filament\Resources\TypeContrats\Tables\TypeContratsTable;
use App\Models\TypeContrat;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class TypeContratResource extends Resource
{
    protected static ?string $model = TypeContrat::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentDuplicate;

    protected static ?string $recordTitleAttribute = 'libelle';

    protected static ?string $navigationLabel = 'Types de Contrats';

    protected static ?string $modelLabel = 'Type de Contrat';

    protected static ?string $pluralModelLabel = 'Types de Contrats';

    public static function getNavigationGroup(): ?string
    {
        return 'Contrats et Garanties';
    }

    protected static ?int $navigationSort = 3;

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
        return TypeContratForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TypeContratInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TypeContratsTable::configure($table);
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
            'index' => ListTypeContrats::route('/'),
            'create' => \App\Filament\Resources\TypeContrats\Pages\CreateTypeContrat::route('/create'),
            'view' => ViewTypeContrat::route('/{record}'),
            'edit' => \App\Filament\Resources\TypeContrats\Pages\EditTypeContrat::route('/{record}/edit'),
        ];
    }
}

