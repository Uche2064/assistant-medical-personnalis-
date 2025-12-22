<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Services\OtpService;
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

        $user = User::where('email', $email)->first();

        if (! $user) {
            Notification::make()
                ->title('Erreur')
                ->body('Aucun compte trouvé avec cet email')
                ->danger()
                ->send();

            return;
        }

        try {
            OtpService::sendForgotPasswordOtp($email);

            session(['forgot_password_email' => $email]);

            Notification::make()
                ->title('Code envoyé')
                ->body('Un code de réinitialisation a été envoyé à votre adresse email')
                ->success()
                ->send();

            $this->redirect(route('filament.admin.pages.verify-otp'));

        } catch (\Throwable $e) {

            Log::error('Erreur OTP', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            Notification::make()
                ->title('Erreur')
                ->body('Une erreur est survenue lors de l\'envoi du code')
                ->danger()
                ->send();
        }
    }

    
}
