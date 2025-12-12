<?php

namespace App\Filament\Resources\Assures;

use App\Enums\RoleEnum;
use App\Filament\Resources\Assures\Pages\CreateAssure;
use App\Filament\Resources\Assures\Pages\EditAssure;
use App\Filament\Resources\Assures\Pages\ListAssures;
use App\Filament\Resources\Assures\Pages\ViewAssure;
use App\Filament\Resources\Assures\Schemas\AssureForm;
use App\Filament\Resources\Assures\Schemas\AssureInfolist;
use App\Filament\Resources\Assures\Tables\AssuresTable;
use App\Models\Assure;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Filament\Facades\Filament;

class AssureResource extends Resource
{
    protected static ?string $model = Assure::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $recordTitleAttribute = 'user.email';

    protected static ?string $navigationLabel = 'Assurés';

    protected static ?string $modelLabel = 'Assuré';

    protected static ?string $pluralModelLabel = 'Assurés';

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return 'Clients';
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Filament::auth()->user() ?? Auth::user();
        if (!$user) {
            return false;
        }

        // Visible pour technicien, medecin_controleur, comptable et admin_global
        return $user->hasRole(RoleEnum::TECHNICIEN->value) ||
               $user->hasRole(RoleEnum::MEDECIN_CONTROLEUR->value) ||
               $user->hasRole(RoleEnum::COMPTABLE->value) ||
               $user->hasRole(RoleEnum::ADMIN_GLOBAL->value);
    }

    public static function canCreate(): bool
    {
        // Les assurés sont créés automatiquement, pas de création manuelle
        return false;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        // Les assurés ne peuvent pas être modifiés manuellement
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return AssureForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AssureInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AssuresTable::configure($table);
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
            'index' => ListAssures::route('/'),
            'view' => ViewAssure::route('/{record}'),
        ];
    }
}

