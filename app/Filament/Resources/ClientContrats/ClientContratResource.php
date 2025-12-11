<?php

namespace App\Filament\Resources\ClientContrats;

use App\Enums\RoleEnum;
use App\Filament\Resources\ClientContrats\Pages\ListClientContrats;
use App\Filament\Resources\ClientContrats\Pages\ViewClientContrat;
use App\Filament\Resources\ClientContrats\Schemas\ClientContratForm;
use App\Filament\Resources\ClientContrats\Schemas\ClientContratInfolist;
use App\Filament\Resources\ClientContrats\Tables\ClientContratsTable;
use App\Models\ClientContrat;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ClientContratResource extends Resource
{
    protected static ?string $model = ClientContrat::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $recordTitleAttribute = 'numero_police';

    protected static ?string $navigationLabel = 'Contrats';

    protected static ?string $modelLabel = 'Contrat';

    protected static ?string $pluralModelLabel = 'Contrats';

    public static function getNavigationGroup(): ?string
    {
        return 'Contrats et Garanties';
    }

    protected static ?int $navigationSort = 4;

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        return $user && $user->hasRole(RoleEnum::MEDECIN_CONTROLEUR->value);
    }

    public static function form(Schema $schema): Schema
    {
        return ClientContratForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ClientContratInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClientContratsTable::configure($table);
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
            'index' => ListClientContrats::route('/'),
            'view' => ViewClientContrat::route('/{record}'),
        ];
    }
}

