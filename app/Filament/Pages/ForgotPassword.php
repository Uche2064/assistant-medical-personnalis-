<?php

namespace App\Filament\Pages;

use App\Enums\OtpTypeEnum;
use App\Jobs\SendEmailJob;
use App\Models\Otp;
use App\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ForgotPassword extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.forgot-password';

    protected static ?string $title = 'Mot de passe oublié';

    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

    /**
     * Permettre l'accès à cette page sans authentification
     */
    public static function canAccess(): bool
    {
        return true; // Accessible sans authentification
    }

    public function mount(): void
    {
        // Si l'utilisateur est déjà connecté, rediriger vers le dashboard
        if (Auth::check()) {
            $this->redirect(route('filament.admin.auth.login'));
        }

        $this->form->fill();
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
                TextInput::make('email')
                    ->label('Adresse email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->autocomplete('email')
                    ->autofocus()
                    ->helperText('Entrez votre adresse email pour recevoir un code de réinitialisation'),
            ])
            ->statePath('data');
    }


    public function sendOtp(): void
    {
        $data = $this->form->getState();
        $email = $data['email'];

        // Vérifier que l'utilisateur existe
        $user = User::where('email', $email)->first();

        if (!$user) {
            Notification::make()
                ->title('Erreur')
                ->body('Aucun compte trouvé avec cet email')
                ->danger()
                ->send();
            return;
        }

        try {
            // Générer et envoyer l'OTP
            $otpExpiredAt = (int) env('OTP_EXPIRED_AT', 10);
            $otp = Otp::generateOtp($email, $otpExpiredAt, OtpTypeEnum::FORGOT_PASSWORD->value);

            Log::info("OTP généré pour mot de passe oublié - Email: {$email}, OTP: {$otp}");

            // Envoyer l'OTP par email
            dispatch(new SendEmailJob(
                $user->email,
                'Réinitialisation de votre mot de passe - SUNU Santé',
                'emails.otp_verification',
                [
                    'user' => $user,
                    'otp' => $otp,
                    'expire_at' => now()->addMinutes($otpExpiredAt),
                ]
            ));

            // Stocker l'email en session pour la page de vérification OTP
            session(['forgot_password_email' => $email]);

            Notification::make()
                ->title('Code envoyé')
                ->body('Un code de réinitialisation a été envoyé à votre adresse email')
                ->success()
                ->send();

            // Rediriger vers la page de vérification OTP
            $this->redirect(route('filament.admin.pages.verify-otp'));

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi de l\'OTP', [
                'error' => $e->getMessage(),
                'email' => $email,
            ]);

            Notification::make()
                ->title('Erreur')
                ->body('Une erreur est survenue lors de l\'envoi du code. Veuillez réessayer.')
                ->danger()
                ->send();
        }
    }
}

