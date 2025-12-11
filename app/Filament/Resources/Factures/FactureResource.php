<?php

namespace App\Filament\Resources\Factures;

use App\Enums\RoleEnum;
use App\Filament\Resources\Factures\Pages\ListFactures;
use App\Filament\Resources\Factures\Pages\ViewFacture;
use App\Filament\Resources\Factures\Schemas\FactureForm;
use App\Filament\Resources\Factures\Schemas\FactureInfolist;
use App\Filament\Resources\Factures\Tables\FacturesTable;
use App\Models\Facture;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class FactureResource extends Resource
{
    protected static ?string $model = Facture::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCurrencyDollar;

    protected static ?string $recordTitleAttribute = 'numero_facture';

    protected static ?string $navigationLabel = 'Factures';

    protected static ?string $modelLabel = 'Facture';

    protected static ?string $pluralModelLabel = 'Factures';

    public static function getNavigationGroup(): ?string
    {
        return 'Facturation';
    }

    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        // Le gestionnaire ne voit pas le menu Factures
        // Visible pour admin_global, technicien, medecin_controleur, comptable, commercial
        $allowedRoles = [
            RoleEnum::ADMIN_GLOBAL->value,
            RoleEnum::TECHNICIEN->value,
            RoleEnum::MEDECIN_CONTROLEUR->value,
            RoleEnum::COMPTABLE->value,
            // RoleEnum::COMMERCIAL->value,
        ];

        foreach ($allowedRoles as $role) {
            if ($user->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return FactureForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return FactureInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FacturesTable::configure($table);
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
            'index' => ListFactures::route('/'),
            'view' => ViewFacture::route('/{record}'),
            // Pas de création ni modification - les factures sont créées par les prestataires via l'API
        ];
    }
}
