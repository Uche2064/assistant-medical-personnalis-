<?php

namespace App\Services;

use App\Enums\StatutDemandeAdhesionEnum;
use App\Models\DemandeAdhesion;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DemandeValidatorService
{
    public function hasPendingDemande(array $data): bool
    {
        return $this->hasDemandeWithStatut($data, StatutDemandeAdhesionEnum::EN_ATTENTE->value);
    }

    public function hasValidatedDemande(array $data): bool
    {
        return $this->hasDemandeWithStatut($data, StatutDemandeAdhesionEnum::VALIDEE->value);
    }

    private function hasDemandeWithStatut(array $data, string $statut): bool
    {

        return User::where(function ($query) use ($data) {
            // On vérifie email et contact obligatoirement
            $query->where('email', $data['email'] ?? Auth::user()->email)
                  ->orWhere('contact', $data['contact'] ?? Auth::user()->contact);
        })
        ->whereHas('demandes', function ($query) use ($statut) {
            $query->where('statut', $statut);
            $query->where('user_id', Auth::user()->id);    
        })
        ->exists();
    }
}
