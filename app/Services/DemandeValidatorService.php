<?php

namespace App\Services;

use App\Enums\StatutValidationEnum;
use App\Models\Prospect;
use Illuminate\Support\Facades\Auth;

class DemandeValidatorService
{
    public function hasPendingDemande(array $data): bool
    {
        return $this->hasDemandeWithStatut($data, StatutValidationEnum::EN_ATTENTE->value);
    }

    public function hasValidatedDemande(array $data): bool
    {
        return $this->hasDemandeWithStatut($data, StatutValidationEnum::VALIDEE->value);
    }

    private function hasDemandeWithStatut(array $data, string $statut): bool
    {
        return Prospect::where(function ($query) use ($data) {
            // On vérifie email et contact obligatoirement
            $query->where('email', $data['email'] ?? Auth::user()->email)
                  ->orWhere('contact', $data['contact'] ?? Auth::user()->contact);

            // Si raison_sociale est présente (ex: entreprise, prestataire), on l'inclut
            if (!empty($data['raison_sociale'])) {
                $query->orWhere('raison_sociale', $data['raison_sociale'] ?? Auth::user()->raison_sociale);
            }
        })
        ->whereHas('demande', function ($query) use ($statut) {
            $query->where('statut', $statut);
        })
        ->exists();
    }
}
