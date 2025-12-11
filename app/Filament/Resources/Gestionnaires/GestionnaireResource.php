<?php

namespace App\Filament\Resources\Gestionnaires;

use App\Enums\RoleEnum;
use App\Filament\Resources\Gestionnaires\Pages\CreateGestionnaire;
use App\Filament\Resources\Gestionnaires\Pages\ListGestionnaires;
use App\Filament\Resources\Gestionnaires\Pages\ViewGestionnaire;
use App\Filament\Resources\Gestionnaires\Schemas\GestionnaireForm;
use App\Filament\Resources\Gestionnaires\Schemas\GestionnaireInfolist;
use App\Filament\Resources\Gestionnaires\Tables\GestionnairesTable;
use App\Models\Personnel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class GestionnaireResource extends Resource
{
    protected static ?string $model = Personnel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserCircle;

    protected static ?string $recordTitleAttribute = 'user.email';
    
    protected static ?string $navigationLabel = 'Gestionnaires';
    
    protected static ?string $modelLabel = 'Gestionnaire';
    
    protected static ?string $pluralModelLabel = 'Gestionnaires';
    
    protected static ?int $navigationSort = 2;
    
    public static function getNavigationGroup(): ?string
    {
        return 'Gestion';
    }
    
    public static function shouldRegisterNavigation(): bool
    {
        // Masquer ce menu - on utilise PersonnelResource à la place
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return GestionnaireForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return GestionnaireInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GestionnairesTable::configure($table);
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
            'index' => ListGestionnaires::route('/'),
            'create' => CreateGestionnaire::route('/create'),
            'view' => ViewGestionnaire::route('/{record}'),
            // Pas de page edit - admin_global ne peut pas modifier les gestionnaires
        ];
    }
}

