<?php

namespace App\Filament\Resources\Personnels\Pages;

use App\Enums\RoleEnum;
use App\Filament\Resources\Personnels\PersonnelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListPersonnels extends ListRecords
{
    protected static string $resource = PersonnelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();
        $user = Auth::user();

        // Charger les relations nécessaires pour l'affichage
        $query->with(['user.personne', 'user.roles', 'gestionnaire.user']);

        // Si c'est un gestionnaire, ne voir que les personnels qu'il a ajoutés
        if ($user && $user->hasRole(RoleEnum::GESTIONNAIRE->value)) {
            if ($user->personnel) {
                // Filtrer uniquement les personnels avec gestionnaire_id = ID du gestionnaire connecté
                $query->where('gestionnaire_id', $user->personnel->id);
            } else {
                // Si le gestionnaire n'a pas de personnel associé, retourner une requête vide
                $query->whereRaw('1 = 0');
            }
        }

        return $query;
    }
}
