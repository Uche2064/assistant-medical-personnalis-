<?php

namespace App\Filament\Resources\Personnels;

use App\Enums\RoleEnum;
use App\Filament\Resources\Personnels\Pages\CreatePersonnel;
use App\Filament\Resources\Personnels\Pages\ListPersonnels;
use App\Filament\Resources\Personnels\Pages\ViewPersonnel;
use App\Filament\Resources\Personnels\Schemas\PersonnelForm;
use App\Filament\Resources\Personnels\Schemas\PersonnelInfolist;
use App\Filament\Resources\Personnels\Tables\PersonnelsTable;
use App\Models\Personnel;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class PersonnelResource extends Resource
{
    protected static ?string $model = Personnel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $recordTitleAttribute = 'user.email';

    protected static ?string $navigationLabel = 'Personnel';

    protected static ?string $modelLabel = 'Personnel';

    protected static ?string $pluralModelLabel = 'Personnels';

    public static function getNavigationGroup(): ?string
    {
        return 'Gestion';
    }

    protected static ?int $navigationSort = 2;

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user() ?? Filament::auth()->user();
        return $user && (
            $user->hasRole(RoleEnum::ADMIN_GLOBAL->value) ||
            $user->hasRole(RoleEnum::GESTIONNAIRE->value)
        );
    }

    public static function canCreate(): bool
    {
        $user = Auth::user() ?? Filament::auth()->user();
        return $user && (
            $user->hasRole(RoleEnum::ADMIN_GLOBAL->value) ||
            $user->hasRole(RoleEnum::GESTIONNAIRE->value)
        );
    }

    public static function canEdit(Model $record): bool
    {
        // Personne ne peut modifier les informations d'un personnel
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        // Personne ne peut supprimer un personnel
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return PersonnelForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PersonnelInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PersonnelsTable::configure($table);
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
            'index' => ListPersonnels::route('/'),
            'create' => CreatePersonnel::route('/create'),
            'view' => ViewPersonnel::route('/{record}'),
        ];
    }
}
