<?php

namespace App\Filament\Pages;

use App\Enums\RoleEnum;
use App\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ResetPassword extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.reset-password';

    protected static ?string $title = 'Réinitialiser le mot de passe';

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
        // Vérifier que l'email est en session (venant de VerifyOtp)
        if (!session()->has('reset_password_email') || !session()->has('is_password_reset')) {
            // Rediriger vers la page mot de passe oublié
            $this->redirect(route('filament.admin.pages.forgot-password'));
        }
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
                TextInput::make('new_password')
                    ->label('Nouveau mot de passe')
                    ->password()
                    ->required()
                    ->minLength(8)
                    ->confirmed()
                    ->helperText('Le mot de passe doit contenir au moins 8 caractères'),

                TextInput::make('new_password_confirmation')
                    ->label('Confirmer le nouveau mot de passe')
                    ->password()
                    ->required()
                    ->same('new_password'),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $email = session('reset_password_email');

        if (!$email) {
            Notification::make()
                ->title('Erreur')
                ->body('Session expirée. Veuillez recommencer.')
                ->danger()
                ->send();

            $this->redirect(route('filament.admin.pages.forgot-password'));
            return;
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            Notification::make()
                ->title('Erreur')
                ->body('Utilisateur non trouvé')
                ->danger()
                ->send();
            return;
        }

        // Vérifier que le nouveau mot de passe est différent de l'ancien
        if (Hash::check($data['new_password'], $user->password)) {
            Notification::make()
                ->title('Erreur')
                ->body('Le nouveau mot de passe doit être différent de l\'ancien')
                ->danger()
                ->send();
            return;
        }

        // Mettre à jour le mot de passe
        $user->update([
            'password' => Hash::make($data['new_password']),
            'mot_de_passe_a_changer' => false,
            'est_actif' => true,
        ]);

        // Nettoyer la session
        session()->forget(['reset_password_email', 'is_password_reset', 'forgot_password_email']);

        Notification::make()
            ->title('Succès')
            ->body('Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.')
            ->success()
            ->send();

        // Rediriger vers la page de login
        $this->redirect(route('filament.admin.auth.login'));
    }
}

