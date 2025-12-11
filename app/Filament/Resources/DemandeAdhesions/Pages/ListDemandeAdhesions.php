<?php

namespace App\Filament\Resources\DemandeAdhesions\Pages;

use App\Enums\RoleEnum;
use App\Filament\Resources\DemandeAdhesions\DemandeAdhesionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

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

        // Pour le médecin contrôleur, filtrer uniquement les demandes prestataires
        $user = Auth::user();
        if ($user && $user->hasRole(RoleEnum::MEDECIN_CONTROLEUR->value)) {
            $query->where('type_demandeur', \App\Enums\TypeDemandeurEnum::PRESTATAIRE->value);
        }

        return $query;
    }
}
