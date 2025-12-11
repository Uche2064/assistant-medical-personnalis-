<?php

namespace App\Filament\Resources\ClientPrestataires;

use App\Enums\RoleEnum;
use App\Filament\Resources\ClientPrestataires\Pages\ListClientPrestataires;
use App\Filament\Resources\ClientPrestataires\Pages\ViewClientPrestataire;
use App\Filament\Resources\ClientPrestataires\Schemas\ClientPrestataireForm;
use App\Filament\Resources\ClientPrestataires\Schemas\ClientPrestataireInfolist;
use App\Filament\Resources\ClientPrestataires\Tables\ClientPrestatairesTable;
use App\Models\ClientPrestataire;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class ClientPrestataireResource extends Resource
{
    protected static ?string $model = ClientPrestataire::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $navigationLabel = 'Réseau de prestataires';

    protected static ?string $modelLabel = 'Réseau de prestataires';

    protected static ?string $pluralModelLabel = 'Réseaux de prestataires';

    public static function getNavigationGroup(): ?string
    {
        return 'Clients et Prestataires';
    }

    protected static ?int $navigationSort = 5;

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
        return ClientPrestataireForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ClientPrestataireInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClientPrestatairesTable::configure($table);
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
            'index' => ListClientPrestataires::route('/'),
            'view' => ViewClientPrestataire::route('/{record}'),
        ];
    }
}

