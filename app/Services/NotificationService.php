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

    /**
     * Crée une notification in-app pour un utilisateur.
     *
     * @param int $userId L'ID de l'utilisateur.
     * @param string $titre Le titre de la notification.
     * @param string $message Le message de la notification.
     * @param string $type Le type de notification.
     * @param array|null $data Les données supplémentaires.
     * @return \App\Models\Notification
     */
    public function createNotification(int $userId, string $titre, string $message, string $type = 'info', array $data = null)
    {
        return \App\Models\Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'titre' => $titre,
            'message' => $message,
            'data' => $data,
            'lu' => false,
        ]);
    }

    /**
     * Envoie un email de confirmation de demande d'adhésion
     *
     * @param \App\Models\DemandeAdhesion $demande La demande d'adhésion
     * @return void
     */
    public function sendDemandeAdhesionConfirmation(\App\Models\DemandeAdhesion $demande): void
    {
        $subject = 'Confirmation de votre demande d\'adhésion - SUNU Santé';
        $view = 'emails.demande_adhesion_physique';
        $data = [
            'demande' => $demande,
            'user' => $demande->user,
        ];

        $this->sendEmail($demande->user->email, $subject, $view, $data);
    }

    /**
     * Notifier les techniciens d'un nouveau compte créé
     *
     * @param User $user L'utilisateur qui a créé le compte
     * @param string $userType Le type d'utilisateur (physique, entreprise)
     * @return void
     */
    public function notifyTechniciensNouveauCompte(User $user, string $userType): void
    {
        // Récupérer tous les techniciens
        $techniciens = User::whereHas('roles', function ($query) {
            $query->where('name', 'technicien');
        })->get();

        foreach ($techniciens as $technicien) {
            $this->createNotification(
                $technicien->id,
                'Nouveau compte créé',
                "Un nouveau compte de type {$userType} a été créé : {$user->email}",
                'info',
                [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'user_type' => $userType,
                    'date_creation' => now()->format('d/m/Y à H:i'),
                    'type_notification' => 'nouveau_compte'
                ]
            );
        }
    }

    /**
     * Notifier les médecins contrôleurs d'un nouveau prestataire
     *
     * @param User $user L'utilisateur prestataire
     * @return void
     */
    public function notifyMedecinsControleursNouveauPrestataire(User $user): void
    {
        // Récupérer tous les médecins contrôleurs
        $medecinsControleurs = User::whereHas('roles', function ($query) {
            $query->where('name', 'medecin_controleur');
        })->get();

        foreach ($medecinsControleurs as $medecin) {
            $this->createNotification(
                $medecin->id,
                'Nouveau prestataire inscrit',
                "Un nouveau prestataire s'est inscrit : {$user->email}",
                'info',
                [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'prestataire_id' => $user->prestataire->id ?? null,
                    'date_inscription' => now()->format('d/m/Y à H:i'),
                    'type_notification' => 'nouveau_prestataire'
                ]
            );
        }
    }

    /**
     * Notifier les techniciens d'une nouvelle demande d'adhésion
     *
     * @param \App\Models\DemandeAdhesion $demande La demande d'adhésion
     * @return void
     */
    public function notifyTechniciensNouvelleDemande(\App\Models\DemandeAdhesion $demande): void
    {
        // Récupérer tous les techniciens
        $techniciens = User::whereHas('roles', function ($query) {
            $query->where('name', 'technicien');
        })->get();

        $userType = $demande->type_demandeur->value;
        $userEmail = $demande->user->email;

        foreach ($techniciens as $technicien) {
            $this->createNotification(
                $technicien->id,
                'Nouvelle demande d\'adhésion',
                "Une nouvelle demande d'adhésion {$userType} a été soumise : {$userEmail}",
                'info',
                [
                    'demande_id' => $demande->id,
                    'user_id' => $demande->user->id,
                    'user_email' => $userEmail,
                    'type_demandeur' => $userType,
                    'date_soumission' => $demande->created_at->format('d/m/Y à H:i'),
                    'type_notification' => 'nouvelle_demande_adhésion'
                ]
            );
        }
    }

    /**
     * Notifier les médecins contrôleurs d'une demande prestataire
     *
     * @param \App\Models\DemandeAdhesion $demande La demande d'adhésion prestataire
     * @return void
     */
    public function notifyMedecinsControleursDemandePrestataire(\App\Models\DemandeAdhesion $demande): void
    {
        // Récupérer tous les médecins contrôleurs
        $medecinsControleurs = User::whereHas('roles', function ($query) {
            $query->where('name', 'medecin_controleur');
        })->get();

        $prestataireEmail = $demande->user->email;

        foreach ($medecinsControleurs as $medecin) {
            $this->createNotification(
                $medecin->id,
                'Nouvelle demande prestataire',
                "Une nouvelle demande d'adhésion prestataire a été soumise : {$prestataireEmail}",
                'info',
                [
                    'demande_id' => $demande->id,
                    'user_id' => $demande->user->id,
                    'user_email' => $prestataireEmail,
                    'prestataire_id' => $demande->user->prestataire->id ?? null,
                    'date_soumission' => $demande->created_at->format('d/m/Y à H:i'),
                    'type_notification' => 'nouvelle_demande_prestataire'
                ]
            );
        }
    }


}
