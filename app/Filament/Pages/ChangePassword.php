<?php

namespace App\Filament\Pages;

use App\Enums\RoleEnum;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Filament\Notifications\Notification;

class ChangePassword extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.change-password';

    protected static ?string $title = 'Changer le mot de passe';

    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

    public function mount(): void
    {
        $user = Auth::user();
        
        // Si l'utilisateur n'a pas besoin de changer son mot de passe, rediriger vers le dashboard
        if (!$user || !$user->mot_de_passe_a_changer) {
            $this->redirect($this->getDashboardUrl());
        }
    }

    protected function getDashboardUrl(): string
    {
        $user = Auth::user();
        
        if (!$user) {
            return route('filament.admin.auth.login');
        }

        // Rediriger vers le dashboard approprié selon le rôle
        if ($user->hasRole(RoleEnum::ADMIN_GLOBAL->value)) {
            return AdminGlobalDashboard::getUrl();
        } elseif ($user->hasRole(RoleEnum::GESTIONNAIRE->value)) {
            return GestionnaireDashboard::getUrl();
        } elseif ($user->hasRole(RoleEnum::COMMERCIAL->value)) {
            return CommercialDashboard::getUrl();
        } elseif ($user->hasRole(RoleEnum::TECHNICIEN->value)) {
            return TechnicienDashboard::getUrl();
        } elseif ($user->hasRole(RoleEnum::MEDECIN_CONTROLEUR->value)) {
            return MedecinControleurDashboard::getUrl();
        } elseif ($user->hasRole(RoleEnum::COMPTABLE->value)) {
            return ComptableDashboard::getUrl();
        }

        // Par défaut, rediriger vers la page de login
        return route('filament.admin.auth.login');
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
                TextInput::make('current_password')
                    ->label('Mot de passe actuel')
                    ->password()
                    ->required()
                    ->helperText('Entrez le mot de passe temporaire qui vous a été envoyé par email'),
                
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
        $user = Auth::user();

        if (!$user) {
            Notification::make()
                ->title('Erreur')
                ->body('Utilisateur non trouvé')
                ->danger()
                ->send();
            return;
        }

        // Vérifier le mot de passe actuel
        if (!Hash::check($data['current_password'], $user->password)) {
            Notification::make()
                ->title('Erreur')
                ->body('Le mot de passe actuel est incorrect')
                ->danger()
                ->send();
            return;
        }

        // Vérifier que le nouveau mot de passe est différent
        if (Hash::check($data['new_password'], $user->password)) {
            Notification::make()
                ->title('Erreur')
                ->body('Le nouveau mot de passe doit être différent du mot de passe actuel')
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

        Notification::make()
            ->title('Succès')
            ->body('Votre mot de passe a été modifié avec succès')
            ->success()
            ->send();

        // Rediriger vers le dashboard approprié
        $this->redirect($this->getDashboardUrl());
    }
}

