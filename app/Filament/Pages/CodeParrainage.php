<?php

namespace App\Filament\Pages;

use App\Enums\RoleEnum;
use App\Models\CommercialParrainageCode;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class CodeParrainage extends Page
{
    protected static ?string $navigationLabel = 'Code Parrainage';

    protected static ?string $title = 'Gestion du Code Parrainage';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.code-parrainage';

    public static function shouldRegisterNavigation(): bool
    {
        // Désactivé - maintenant géré par CommercialParrainageCodeResource
        return false;
    }

    public function mount(): void
    {
        $user = Auth::user();

        if (!$user->hasRole(RoleEnum::COMMERCIAL->value)) {
            abort(403, 'Accès non autorisé');
        }
    }

    public function getCurrentCode()
    {
        $commercial = Auth::user();
        return CommercialParrainageCode::getCurrentCode($commercial->id);
    }

    public function getHistory()
    {
        $commercial = Auth::user();
        return CommercialParrainageCode::getHistory($commercial->id)
            ->map(function ($code) {
                return [
                    'id' => $code->id,
                    'code_parrainage' => $code->code_parrainage,
                    'date_debut' => $code->date_debut->format('d/m/Y H:i'),
                    'date_expiration' => $code->date_expiration->format('d/m/Y H:i'),
                    'est_actif' => $code->est_actif,
                    'est_renouvele' => $code->est_renouvele,
                    'est_expire' => $code->isExpired(),
                    'jours_restants' => $code->isExpired() ? 0 : now()->diffInDays($code->date_expiration, false),
                    'created_at' => $code->created_at->format('d/m/Y H:i'),
                ];
            });
    }
}
