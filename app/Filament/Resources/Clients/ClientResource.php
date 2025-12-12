<?php

namespace App\Filament\Resources\Clients;

use App\Enums\RoleEnum;
use App\Filament\Resources\Clients\Pages\CreateClient;
use App\Filament\Resources\Clients\Pages\EditClient;
use App\Filament\Resources\Clients\Pages\ListClients;
use App\Filament\Resources\Clients\Pages\ViewClient;
use App\Filament\Resources\Clients\Schemas\ClientForm;
use App\Filament\Resources\Clients\Schemas\ClientInfolist;
use App\Filament\Resources\Clients\Tables\ClientsTable;
use App\Models\Client;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Filament\Facades\Filament;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;

    protected static ?string $recordTitleAttribute = 'user.email';

    protected static ?string $navigationLabel = 'Clients';

    protected static ?string $modelLabel = 'Client';

    protected static ?string $pluralModelLabel = 'Clients';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return 'Clients';
    }

    public static function getNavigationBadge(): ?string
    {
        $user = Filament::auth()->user() ?? Auth::user();
        if (!$user) {
            return null;
        }

        // Pour les commerciaux, afficher le nombre de notifications non lues liées aux nouveaux clients parrainés
        if ($user->hasRole(RoleEnum::COMMERCIAL->value)) {
            $unreadCount = \App\Models\Notification::where('user_id', $user->id)
                ->where('est_lu', false)
                ->get()
                ->filter(function ($notification) {
                    $data = $notification->data ?? [];
                    $typeNotification = $data['type_notification'] ?? null;
                    return $typeNotification === 'nouveau_client_parraine';
                })
                ->count();

            return $unreadCount > 0 ? (string) $unreadCount : null;
        }

        return null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Filament::auth()->user() ?? Auth::user();
        if (!$user) {
            return false;
        }

        // Visible pour commercial, admin_global et technicien
        return $user->hasRole(RoleEnum::COMMERCIAL->value) ||
               $user->hasRole(RoleEnum::ADMIN_GLOBAL->value) ||
               $user->hasRole(RoleEnum::TECHNICIEN->value);
    }

    public static function form(Schema $schema): Schema
    {
        return ClientForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ClientInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClientsTable::configure($table);
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
            'index' => ListClients::route('/'),
            'create' => CreateClient::route('/create'),
            'view' => ViewClient::route('/{record}'),
            'edit' => EditClient::route('/{record}/edit'),
        ];
    }
}
