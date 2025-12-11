<?php

namespace App\Filament\Resources\DemandeAdhesions\Pages;

use App\Enums\RoleEnum;
use App\Filament\Resources\DemandeAdhesions\DemandeAdhesionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Filament\Facades\Filament;

class ListDemandeAdhesions extends ListRecords
{
    protected static string $resource = DemandeAdhesionResource::class;

    protected function getHeaderActions(): array
    {
        // Vérifier si l'utilisateur peut créer des demandes
        if (DemandeAdhesionResource::canCreate()) {
            return [
                CreateAction::make(),
            ];
        }

        return [];
    }

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getTableQuery();

        $user = Filament::auth()->user() ?? Auth::user();

        if (!$user) {
            return $query->whereRaw('1 = 0'); // Aucun résultat si pas d'utilisateur
        }

        // Pour le médecin contrôleur, filtrer uniquement les demandes prestataires
        if ($user->hasRole(RoleEnum::MEDECIN_CONTROLEUR->value)) {
            $query->where('type_demandeur', \App\Enums\TypeDemandeurEnum::PRESTATAIRE->value);
        }

        // Pour les techniciens, filtrer uniquement les demandes client (physique et moral)
        // Le type_demandeur "client" inclut à la fois les personnes physiques et morales
        if ($user->hasRole(RoleEnum::TECHNICIEN->value)) {
            $query->where('type_demandeur', \App\Enums\TypeDemandeurEnum::CLIENT->value);
        }

        return $query;
    }
}
