<?php

namespace App\Filament\Resources\Clients\Pages;

use App\Enums\RoleEnum;
use App\Filament\Resources\Clients\ClientResource;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListClients extends ListRecords
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function mount(): void
    {
        parent::mount();

        // Marquer les notifications liées aux nouveaux clients comme lues quand on accède à la page
        $user = Filament::auth()->user() ?? Auth::user();
        if ($user && $user->hasRole(RoleEnum::COMMERCIAL->value)) {
            \App\Models\Notification::where('user_id', $user->id)
                ->where('est_lu', false)
                ->get()
                ->filter(function ($notification) {
                    $data = $notification->data ?? [];
                    $typeNotification = $data['type_notification'] ?? null;
                    return $typeNotification === 'nouveau_client_parraine';
                })
                ->each(function ($notification) {
                    $notification->markAsRead();
                });
        }
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        // Si l'utilisateur est un commercial, filtrer seulement ses clients
        $user = Filament::auth()->user() ?? Auth::user();

        if ($user && $user->hasRole(RoleEnum::COMMERCIAL->value)) {
            // Filtrer directement par commercial_id dans la table clients
            $query->where('commercial_id', $user->id);
        }
        // Si admin_global ou technicien, voir tous les clients (pas de filtre)

        return $query;
    }
}
