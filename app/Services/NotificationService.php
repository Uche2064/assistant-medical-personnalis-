<?php

namespace App\Services;

use App\Mail\GenericMail;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Envoie un e-mail générique.
     *
     * @param string $recipientEmail L'adresse e-mail du destinataire.
     * @param string $subject Le sujet de l'e-mail.
     * @param string $view La vue Blade à utiliser pour le corps de l'e-mail.
     * @param array $data Les données à passer à la vue.
     * @return void
     */
    public function sendEmail(string $recipientEmail, string $subject, string $view, array $data): void
    {
        try {
            Log::alert("Sending....");
            Mail::to($recipientEmail)->send(new GenericMail($subject, $view, $data));
        } catch (\Exception $e) {
            // Gérer l'échec de l'envoi de l'e-mail, par exemple, en loguant l'erreur.
            Log::error("Erreur d'envoi de mail: " . $e->getMessage());
        }
    }

    /**
     * Envoie les identifiants de connexion à un utilisateur.
     *
     * @param User $user L'utilisateur à qui envoyer les identifiants.
     * @param string $plainPassword Le mot de passe en clair.
     * @return void
     */
    public function sendCredentials(User $user, string $plainPassword): void
    {
        $subject = 'Vos identifiants de connexion';
        $view = 'emails.credentials';
        $data = [
            'user' => $user,
            'password' => $plainPassword,
        ];

        $this->sendEmail($user->email, $subject, $view, $data);
    }
}
