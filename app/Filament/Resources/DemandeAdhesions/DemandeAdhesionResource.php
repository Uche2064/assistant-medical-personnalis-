<?php

namespace App\Filament\Resources\DemandeAdhesions;

use App\Filament\Resources\DemandeAdhesions\Pages\CreateDemandeAdhesion;
use App\Filament\Resources\DemandeAdhesions\Pages\EditDemandeAdhesion;
use App\Filament\Resources\DemandeAdhesions\Pages\ListDemandeAdhesions;
use App\Filament\Resources\DemandeAdhesions\Pages\ViewDemandeAdhesion;
use App\Filament\Resources\DemandeAdhesions\Schemas\DemandeAdhesionForm;
use App\Filament\Resources\DemandeAdhesions\Schemas\DemandeAdhesionInfolist;
use App\Filament\Resources\DemandeAdhesions\Tables\DemandeAdhesionsTable;
use App\Models\DemandeAdhesion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Filament\Facades\Filament;

class DemandeAdhesionResource extends Resource
{
    protected static ?string $model = DemandeAdhesion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $recordTitleAttribute = 'user.email';

    protected static ?string $navigationLabel = 'Demandes d\'adhésion';

    public static function getNavigationLabel(): string
    {
        $user = Filament::auth()->user() ?? Auth::user();
        if ($user && $user->hasRole(\App\Enums\RoleEnum::MEDECIN_CONTROLEUR->value)) {
            return 'Demandes Prestataires';
        }
        return static::$navigationLabel ?? static::getModelLabel();
    }

    protected static ?string $modelLabel = 'Demande d\'adhésion';

    protected static ?string $pluralModelLabel = 'Demandes d\'adhésion';

    public static function getNavigationGroup(): ?string
    {
        return 'Demandes';
    }

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $user = Filament::auth()->user() ?? Auth::user();
        if (!$user) {
            return null;
        }

        // Compter les notifications non lues liées aux demandes d'adhésion
        // Le champ data est casté en array, donc on peut utiliser where avec un callback
        $unreadCount = \App\Models\Notification::where('user_id', $user->id)
            ->where('est_lu', false)
            ->get()
            ->filter(function ($notification) {
                $data = $notification->data ?? [];
                $typeNotification = $data['type_notification'] ?? null;
                return in_array($typeNotification, ['nouvelle_demande_adhésion', 'nouvelle_demande_prestataire']);
            })
            ->count();

        return $unreadCount > 0 ? (string) $unreadCount : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function form(Schema $schema): Schema
    {
        return DemandeAdhesionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DemandeAdhesionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DemandeAdhesionsTable::configure($table);
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
            'index' => ListDemandeAdhesions::route('/'),
            'view' => ViewDemandeAdhesion::route('/{record}'),
            'edit' => EditDemandeAdhesion::route('/{record}/edit'),
            // La page create sera masquée dans ListDemandeAdhesions si l'utilisateur n'a pas le droit
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Filament::auth()->user() ?? Auth::user();
        if (!$user) {
            return false;
        }

        // Visible pour admin_global, technicien et medecin_controleur
        return $user->hasRole(\App\Enums\RoleEnum::ADMIN_GLOBAL->value) ||
               $user->hasRole(\App\Enums\RoleEnum::TECHNICIEN->value) ||
               $user->hasRole(\App\Enums\RoleEnum::MEDECIN_CONTROLEUR->value);
    }

    public static function canCreate(): bool
    {
        $user = Filament::auth()->user() ?? Auth::user();

        if (!$user) {
            return false;
        }

        // Rôles qui ne peuvent pas créer de demandes d'adhésion
        $rolesBloques = [
            \App\Enums\RoleEnum::ADMIN_GLOBAL->value,
            \App\Enums\RoleEnum::GESTIONNAIRE->value,
            \App\Enums\RoleEnum::MEDECIN_CONTROLEUR->value,
            \App\Enums\RoleEnum::TECHNICIEN->value,
            \App\Enums\RoleEnum::COMPTABLE->value,
        ];

        return !$user->hasAnyRole($rolesBloques);
    }

    public static function canEdit($record): bool
    {
        $user = Filament::auth()->user() ?? Auth::user();

        if (!$user) {
            return false;
        }

        // Les techniciens et médecins contrôleurs ne peuvent pas modifier les demandes d'adhésion
        $rolesBloques = [
            \App\Enums\RoleEnum::TECHNICIEN->value,
            \App\Enums\RoleEnum::MEDECIN_CONTROLEUR->value,
        ];

        return !$user->hasAnyRole($rolesBloques);
    }
}
