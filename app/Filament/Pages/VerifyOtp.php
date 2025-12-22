<?php

namespace App\Filament\Pages;

use App\Enums\OtpTypeEnum;
use App\Models\Otp;
use App\Services\OtpService;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class VerifyOtp extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.verify-otp';

    protected static ?string $title = 'Vérification du code';

    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

    public int $resendTimer = 60;

    /**
     * Permettre l'accès à cette page sans authentification
     */
    public static function canAccess(): bool
    {
        return true; // Accessible sans authentification
    }

    public function mount(): void
    {
        // Vérifier que l'email est en session (venant de ForgotPassword)
        if (! session()->has('forgot_password_email')) {
            // Rediriger vers la page mot de passe oublié
            $this->redirect(route('filament.admin.pages.forgot-password'));
        }

        // Si l'utilisateur est déjà connecté, rediriger vers le dashboard
        if (Auth::check()) {
            $this->redirect(route('filament.admin.auth.login'));
        }

        // Initialiser le timer
        $this->resendTimer = 60;
    }

    protected function getForms(): array
    {
        return [
            'form',
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('otp')
                    ->label('Code de vérification')
                    ->required()
                    ->length(6)
                    ->helperText('Entrez le code à 6 chiffres reçu par email'),
            ])
            ->statePath('data');
    }

    public function verifyOtp(): void
    {
        $data = $this->form->getState();
        $otp = $data['otp'];
        $email = session('forgot_password_email');

        if (! $email) {
            Notification::make()
                ->title('Erreur')
                ->body('Session expirée. Veuillez recommencer.')
                ->danger()
                ->send();

            $this->redirect(route('filament.admin.pages.forgot-password'));

            return;
        }
        // Vérifier l'OTP
        $otpRecord = Otp::where('email', $email)
            ->where('otp', $otp)
            ->where('type', OtpTypeEnum::FORGOT_PASSWORD->value)
            ->where('expire_a', '>', now())
            ->first();

        if (! $otpRecord || $otpRecord->isExpired()) {
            Notification::make()
                ->title('Code invalide')
                ->body('Le code de vérification est invalide ou a expiré. Veuillez réessayer.')
                ->danger()
                ->send();

            return;
        }

        // OTP valide - supprimer l'OTP et stocker l'email en session pour la page de changement de mot de passe
        $otpRecord->delete();

        // Stocker l'email et un flag pour indiquer que c'est un reset (pas un changement obligatoire)
        session([
            'reset_password_email' => $email,
            'is_password_reset' => true,
        ]);

        Notification::make()
            ->title('Code validé')
            ->body('Vous pouvez maintenant définir un nouveau mot de passe')
            ->success()
            ->send();

        // Rediriger vers la page de changement de mot de passe
        $this->redirect(route('filament.admin.pages.reset-password'));
    }


    public function resendOtp(): void
    {
        $email = session('forgot_password_email');

        if (! $email) {
            Notification::make()
                ->title('Session expirée')
                ->body('Veuillez recommencer la procédure.')
                ->danger()
                ->send();

            $this->redirect(route('filament.admin.pages.forgot-password'));
            return;
        }

        // Envoi OTP (réutilise ta logique existante)
        OtpService::sendForgotPasswordOtp($email);

        Notification::make()
            ->title('Code renvoyé')
            ->body('Un nouveau code a été envoyé.')
            ->success()
            ->send();

        // Reset du timer (clé 🔑)
        $this->resendTimer = 60;
    }

}
