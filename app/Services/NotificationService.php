<?php

namespace App\Services;

use App\Mail\GenericMail;
use App\Models\DemandeAdhesion;
use App\Models\Notification;
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
     * @return Notification
     */
    public function createNotification(int $userId, string $titre, string $message, string $type = 'info', array $data = null)
    {
        return Notification::create([
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
     * @param DemandeAdhesion $demande La demande d'adhésion
     * @return void
     */
    public function sendDemandeAdhesionConfirmation(DemandeAdhesion $demande): void
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
     * @param DemandeAdhesion $demande La demande d'adhésion
     * @return void
     */
    public function notifyTechniciensNouvelleDemande(DemandeAdhesion $demande): void
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
     * @param DemandeAdhesion $demande La demande d'adhésion prestataire
     * @return void
     */
    public function notifyMedecinsControleursDemandePrestataire(DemandeAdhesion $demande): void
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

    /**
     * Notifier les techniciens, prestataires et médecins contrôleurs d'un nouveau bénéficiaire ajouté
     *
     * @param \App\Models\Assure $beneficiaire Le bénéficiaire ajouté
     * @param \App\Models\User $client Le client qui a ajouté le bénéficiaire
     * @return void
     */
    public function notifyBeneficiaireAjoute(\App\Models\Assure $beneficiaire, \App\Models\User $client): void
    {
        // Récupérer tous les techniciens
        $techniciens = User::whereHas('roles', function ($query) {
            $query->where('name', 'technicien');
        })->get();

        // Récupérer tous les médecins contrôleurs
        $medecinsControleurs = User::whereHas('roles', function ($query) {
            $query->where('name', 'medecin_controleur');
        })->get();

        // Récupérer tous les prestataires
        $prestataires = User::whereHas('roles', function ($query) {
            $query->where('name', 'prestataire');
        })->get();

        $beneficiaireNom = $beneficiaire->nom . ' ' . $beneficiaire->prenoms;
        $clientNom = $client->assure ? $client->assure->nom . ' ' . $client->assure->prenoms : $client->email;

        // Notifier les techniciens
        foreach ($techniciens as $technicien) {
            $this->createNotification(
                $technicien->id,
                'Nouveau bénéficiaire ajouté',
                "Le client {$clientNom} a ajouté un nouveau bénéficiaire : {$beneficiaireNom}",
                'info',
                [
                    'beneficiaire_id' => $beneficiaire->id,
                    'beneficiaire_nom' => $beneficiaireNom,
                    'beneficiaire_lien_parente' => $beneficiaire->lien_parente,
                    'client_id' => $client->id,
                    'client_nom' => $clientNom,
                    'client_email' => $client->email,
                    'date_ajout' => now()->format('d/m/Y à H:i'),
                    'type_notification' => 'beneficiaire_ajoute'
                ]
            );
        }

        // Notifier les médecins contrôleurs
        foreach ($medecinsControleurs as $medecin) {
            $this->createNotification(
                $medecin->id,
                'Nouveau bénéficiaire ajouté',
                "Le client {$clientNom} a ajouté un nouveau bénéficiaire : {$beneficiaireNom}",
                'info',
                [
                    'beneficiaire_id' => $beneficiaire->id,
                    'beneficiaire_nom' => $beneficiaireNom,
                    'beneficiaire_lien_parente' => $beneficiaire->lien_parente,
                    'client_id' => $client->id,
                    'client_nom' => $clientNom,
                    'client_email' => $client->email,
                    'date_ajout' => now()->format('d/m/Y à H:i'),
                    'type_notification' => 'beneficiaire_ajoute'
                ]
            );
        }

        // Notifier les prestataires
        foreach ($prestataires as $prestataire) {
            $this->createNotification(
                $prestataire->id,
                'Nouveau bénéficiaire ajouté',
                "Le client {$clientNom} a ajouté un nouveau bénéficiaire : {$beneficiaireNom}",
                'info',
                [
                    'beneficiaire_id' => $beneficiaire->id,
                    'beneficiaire_nom' => $beneficiaireNom,
                    'beneficiaire_lien_parente' => $beneficiaire->lien_parente,
                    'client_id' => $client->id,
                    'client_nom' => $clientNom,
                    'client_email' => $client->email,
                    'date_ajout' => now()->format('d/m/Y à H:i'),
                    'type_notification' => 'beneficiaire_ajoute'
                ]
            );
        }
    }

    /**
     * Notifier les techniciens, prestataires et médecins contrôleurs d'un bénéficiaire supprimé
     *
     * @param \App\Models\Assure $beneficiaire Le bénéficiaire supprimé
     * @param \App\Models\User $client Le client qui a supprimé le bénéficiaire
     * @return void
     */
    public function notifyBeneficiaireSupprime(\App\Models\Assure $beneficiaire, \App\Models\User $client): void
    {
        // Récupérer tous les techniciens
        $techniciens = User::whereHas('roles', function ($query) {
            $query->where('name', 'technicien');
        })->get();

        // Récupérer tous les médecins contrôleurs
        $medecinsControleurs = User::whereHas('roles', function ($query) {
            $query->where('name', 'medecin_controleur');
        })->get();

        // Récupérer tous les prestataires
        $prestataires = User::whereHas('roles', function ($query) {
            $query->where('name', 'prestataire');
        })->get();

        $beneficiaireNom = $beneficiaire->nom . ' ' . $beneficiaire->prenoms;
        $clientNom = $client->assure ? $client->assure->nom . ' ' . $client->assure->prenoms : $client->email;

        // Notifier les techniciens
        foreach ($techniciens as $technicien) {
            $this->createNotification(
                $technicien->id,
                'Bénéficiaire supprimé',
                "Le client {$clientNom} a supprimé un bénéficiaire : {$beneficiaireNom}",
                'warning',
                [
                    'beneficiaire_id' => $beneficiaire->id,
                    'beneficiaire_nom' => $beneficiaireNom,
                    'beneficiaire_lien_parente' => $beneficiaire->lien_parente,
                    'client_id' => $client->id,
                    'client_nom' => $clientNom,
                    'client_email' => $client->email,
                    'date_suppression' => now()->format('d/m/Y à H:i'),
                    'type_notification' => 'beneficiaire_supprime'
                ]
            );
        }

        // Notifier les médecins contrôleurs
        foreach ($medecinsControleurs as $medecin) {
            $this->createNotification(
                $medecin->id,
                'Bénéficiaire supprimé',
                "Le client {$clientNom} a supprimé un bénéficiaire : {$beneficiaireNom}",
                'warning',
                [
                    'beneficiaire_id' => $beneficiaire->id,
                    'beneficiaire_nom' => $beneficiaireNom,
                    'beneficiaire_lien_parente' => $beneficiaire->lien_parente,
                    'client_id' => $client->id,
                    'client_nom' => $clientNom,
                    'client_email' => $client->email,
                    'date_suppression' => now()->format('d/m/Y à H:i'),
                    'type_notification' => 'beneficiaire_supprime'
                ]
            );
        }

        // Notifier les prestataires
        foreach ($prestataires as $prestataire) {
            $this->createNotification(
                $prestataire->id,
                'Bénéficiaire supprimé',
                "Le client {$clientNom} a supprimé un bénéficiaire : {$beneficiaireNom}",
                'warning',
                [
                    'beneficiaire_id' => $beneficiaire->id,
                    'beneficiaire_nom' => $beneficiaireNom,
                    'beneficiaire_lien_parente' => $beneficiaire->lien_parente,
                    'client_id' => $client->id,
                    'client_nom' => $clientNom,
                    'client_email' => $client->email,
                    'date_suppression' => now()->format('d/m/Y à H:i'),
                    'type_notification' => 'beneficiaire_supprime'
                ]
            );
        }
    }

    /**
     * Notifier les médecins contrôleurs qu'une facture a été validée par un technicien
     *
     * @param \App\Models\Facture $facture La facture validée
     * @return void
     */
    public function notifyValidationTechnicien(\App\Models\Facture $facture): void
    {
        // Récupérer tous les médecins contrôleurs
        $medecinsControleurs = User::whereHas('roles', function ($query) {
            $query->where('name', 'medecin_controleur');
        })->get();

        $prestataire = $facture->prestataire;
        $assure = $facture->sinistre->assure;
        $montantFormatted = number_format($facture->montant_reclame, 0, ',', ' ') . ' FCFA';

        foreach ($medecinsControleurs as $medecin) {
            $this->createNotification(
                $medecin->id,
                'Facture validée par technicien',
                "Facture {$facture->numero_facture} de {$prestataire->raison_sociale} validée par le technicien - {$montantFormatted}",
                'info',
                [
                    'facture_id' => $facture->id,
                    'numero_facture' => $facture->numero_facture,
                    'prestataire_id' => $prestataire->id,
                    'prestataire_nom' => $prestataire->raison_sociale,
                    'assure_id' => $assure->id,
                    'assure_nom' => $assure->nom . ' ' . $assure->prenoms,
                    'montant_reclame' => $facture->montant_reclame,
                    'montant_formatted' => $montantFormatted,
                    'date_validation' => $facture->valide_par_technicien_a->format('d/m/Y à H:i'),
                    'type_notification' => 'facture_validee_technicien'
                ]
            );
        }
    }

    /**
     * Notifier tous les acteurs concernés qu'une facture a été rejetée par un technicien
     *
     * @param \App\Models\Facture $facture La facture rejetée
     * @return void
     */
    public function notifyRejetTechnicien(\App\Models\Facture $facture): void
    {
        $prestataire = $facture->prestataire;
        $assure = $facture->sinistre->assure;
        $montantFormatted = number_format($facture->montant_reclame, 0, ',', ' ') . ' FCFA';

        // 1. Notifier le prestataire
        if ($prestataire && $prestataire->user) {
            $this->createNotification(
                $prestataire->user->id,
                'Facture rejetée par technicien',
                "Votre facture {$facture->numero_facture} a été rejetée par le technicien : {$facture->motif_rejet_technicien}",
                'error',
                [
                    'facture_id' => $facture->id,
                    'numero_facture' => $facture->numero_facture,
                    'assure_id' => $assure->id,
                    'assure_nom' => $assure->nom . ' ' . $assure->prenoms,
                    'montant_reclame' => $facture->montant_reclame,
                    'montant_formatted' => $montantFormatted,
                    'motif_rejet' => $facture->motif_rejet_technicien,
                    'date_rejet' => $facture->rejet_par_technicien_a->format('d/m/Y à H:i'),
                    'type_notification' => 'facture_rejetee_technicien'
                ]
            );
        }

        // 2. Notifier l'assuré
        if ($assure && $assure->user) {
            $this->createNotification(
                $assure->user->id,
                'Facture rejetée par technicien',
                "Votre facture {$facture->numero_facture} a été rejetée par le technicien : {$facture->motif_rejet_technicien}",
                'error',
                [
                    'facture_id' => $facture->id,
                    'numero_facture' => $facture->numero_facture,
                    'prestataire_id' => $prestataire->id,
                    'prestataire_nom' => $prestataire->raison_sociale,
                    'montant_reclame' => $facture->montant_reclame,
                    'montant_formatted' => $montantFormatted,
                    'motif_rejet' => $facture->motif_rejet_technicien,
                    'date_rejet' => $facture->rejet_par_technicien_a->format('d/m/Y à H:i'),
                    'type_notification' => 'facture_rejetee_technicien_assure'
                ]
            );
        }

        // 3. Notifier tous les médecins contrôleurs
        $medecinsControleurs = User::whereHas('roles', function ($query) {
            $query->where('name', 'medecin_controleur');
        })->get();

        foreach ($medecinsControleurs as $medecin) {
            $this->createNotification(
                $medecin->id,
                'Facture rejetée par technicien',
                "Facture {$facture->numero_facture} rejetée par le technicien - {$montantFormatted}",
                'warning',
                [
                    'facture_id' => $facture->id,
                    'numero_facture' => $facture->numero_facture,
                    'prestataire_id' => $prestataire->id,
                    'prestataire_nom' => $prestataire->raison_sociale,
                    'assure_id' => $assure->id,
                    'assure_nom' => $assure->nom . ' ' . $assure->prenoms,
                    'montant_reclame' => $facture->montant_reclame,
                    'montant_formatted' => $montantFormatted,
                    'motif_rejet' => $facture->motif_rejet_technicien,
                    'date_rejet' => $facture->rejet_par_technicien_a->format('d/m/Y à H:i'),
                    'type_notification' => 'facture_rejetee_technicien_medecin'
                ]
            );
        }
    }

    /**
     * Notifier les comptables qu'une facture a été validée par un médecin contrôleur
     *
     * @param \App\Models\Facture $facture La facture validée
     * @return void
     */
    public function notifyValidationMedecin(\App\Models\Facture $facture): void
    {
        // Récupérer tous les comptables
        $comptables = User::whereHas('roles', function ($query) {
            $query->where('name', 'comptable');
        })->get();

        $prestataire = $facture->prestataire;
        $assure = $facture->sinistre->assure;
        $montantFormatted = number_format($facture->montant_reclame, 0, ',', ' ') . ' FCFA';

        foreach ($comptables as $comptable) {
            $this->createNotification(
                $comptable->id,
                'Facture validée par médecin contrôleur',
                "Facture {$facture->numero_facture} de {$prestataire->raison_sociale} validée par le médecin contrôleur - {$montantFormatted}",
                'info',
                [
                    'facture_id' => $facture->id,
                    'numero_facture' => $facture->numero_facture,
                    'prestataire_id' => $prestataire->id,
                    'prestataire_nom' => $prestataire->raison_sociale,
                    'assure_id' => $assure->id,
                    'assure_nom' => $assure->nom . ' ' . $assure->prenoms,
                    'montant_reclame' => $facture->montant_reclame,
                    'montant_formatted' => $montantFormatted,
                    'date_validation' => $facture->valide_par_medecin_a->format('d/m/Y à H:i'),
                    'type_notification' => 'facture_validee_medecin'
                ]
            );
        }
    }

    /**
     * Notifier tous les acteurs concernés qu'une facture a été rejetée par un médecin contrôleur
     *
     * @param \App\Models\Facture $facture La facture rejetée
     * @return void
     */
    public function notifyRejetMedecin(\App\Models\Facture $facture): void
    {
        $prestataire = $facture->prestataire;
        $assure = $facture->sinistre->assure;
        $montantFormatted = number_format($facture->montant_reclame, 0, ',', ' ') . ' FCFA';

        // 1. Notifier le prestataire
        if ($prestataire && $prestataire->user) {
            $this->createNotification(
                $prestataire->user->id,
                'Facture rejetée par médecin contrôleur',
                "Votre facture {$facture->numero_facture} a été rejetée par le médecin contrôleur : {$facture->motif_rejet_medecin}",
                'error',
                [
                    'facture_id' => $facture->id,
                    'numero_facture' => $facture->numero_facture,
                    'assure_id' => $assure->id,
                    'assure_nom' => $assure->nom . ' ' . $assure->prenoms,
                    'montant_reclame' => $facture->montant_reclame,
                    'montant_formatted' => $montantFormatted,
                    'motif_rejet' => $facture->motif_rejet_medecin,
                    'date_rejet' => $facture->rejet_par_medecin_a->format('d/m/Y à H:i'),
                    'type_notification' => 'facture_rejetee_medecin'
                ]
            );
        }

        // 2. Notifier l'assuré
        if ($assure && $assure->user) {
            $this->createNotification(
                $assure->user->id,
                'Facture rejetée par médecin contrôleur',
                "Votre facture {$facture->numero_facture} a été rejetée par le médecin contrôleur : {$facture->motif_rejet_medecin}",
                'error',
                [
                    'facture_id' => $facture->id,
                    'numero_facture' => $facture->numero_facture,
                    'prestataire_id' => $prestataire->id,
                    'prestataire_nom' => $prestataire->raison_sociale,
                    'montant_reclame' => $facture->montant_reclame,
                    'montant_formatted' => $montantFormatted,
                    'motif_rejet' => $facture->motif_rejet_medecin,
                    'date_rejet' => $facture->rejet_par_medecin_a->format('d/m/Y à H:i'),
                    'type_notification' => 'facture_rejetee_medecin_assure'
                ]
            );
        }

        // 3. Notifier le technicien qui a validé
        if ($facture->technicien && $facture->technicien->user) {
            $this->createNotification(
                $facture->technicien->user->id,
                'Facture rejetée par médecin contrôleur',
                "Facture {$facture->numero_facture} que vous avez validée a été rejetée par le médecin contrôleur - {$montantFormatted}",
                'warning',
                [
                    'facture_id' => $facture->id,
                    'numero_facture' => $facture->numero_facture,
                    'prestataire_id' => $prestataire->id,
                    'prestataire_nom' => $prestataire->raison_sociale,
                    'assure_id' => $assure->id,
                    'assure_nom' => $assure->nom . ' ' . $assure->prenoms,
                    'montant_reclame' => $facture->montant_reclame,
                    'montant_formatted' => $montantFormatted,
                    'motif_rejet' => $facture->motif_rejet_medecin,
                    'date_rejet' => $facture->rejet_par_medecin_a->format('d/m/Y à H:i'),
                    'type_notification' => 'facture_rejetee_medecin_technicien'
                ]
            );
        }

        // 4. Notifier tous les comptables
        $comptables = User::whereHas('roles', function ($query) {
            $query->where('name', 'comptable');
        })->get();

        foreach ($comptables as $comptable) {
            $this->createNotification(
                $comptable->id,
                'Facture rejetée par médecin contrôleur',
                "Facture {$facture->numero_facture} rejetée par le médecin contrôleur - {$montantFormatted}",
                'warning',
                [
                    'facture_id' => $facture->id,
                    'numero_facture' => $facture->numero_facture,
                    'prestataire_id' => $prestataire->id,
                    'prestataire_nom' => $prestataire->raison_sociale,
                    'assure_id' => $assure->id,
                    'assure_nom' => $assure->nom . ' ' . $assure->prenoms,
                    'montant_reclame' => $facture->montant_reclame,
                    'montant_formatted' => $montantFormatted,
                    'motif_rejet' => $facture->motif_rejet_medecin,
                    'date_rejet' => $facture->rejet_par_medecin_a->format('d/m/Y à H:i'),
                    'type_notification' => 'facture_rejetee_medecin_comptable'
                ]
            );
        }
    }

    /**
     * Notifier le prestataire qu'une facture a été autorisée par un comptable
     *
     * @param \App\Models\Facture $facture La facture autorisée
     * @return void
     */
    public function notifyAutorisationComptable(\App\Models\Facture $facture): void
    {
        $prestataire = $facture->prestataire;
        $assure = $facture->sinistre->assure;
        $montantFormatted = number_format($facture->montant_reclame, 0, ',', ' ') . ' FCFA';

        $this->createNotification(
            $prestataire->user->id,
            'Facture autorisée par comptable',
            "Votre facture {$facture->numero_facture} a été autorisée par le comptable pour remboursement",
            'success',
            [
                'facture_id' => $facture->id,
                'numero_facture' => $facture->numero_facture,
                'assure_id' => $assure->id,
                'assure_nom' => $assure->nom . ' ' . $assure->prenoms,
                'montant_reclame' => $facture->montant_reclame,
                'montant_formatted' => $montantFormatted,
                'date_autorisation' => $facture->autorise_par_comptable_a->format('d/m/Y à H:i'),
                'type_notification' => 'facture_autorisee_comptable'
            ]
        );
    }

    /**
     * Notifier tous les acteurs concernés qu'une facture a été rejetée par un comptable
     *
     * @param \App\Models\Facture $facture La facture rejetée
     * @return void
     */
    public function notifyRejetComptable(\App\Models\Facture $facture): void
    {
        $prestataire = $facture->prestataire;
        $assure = $facture->sinistre->assure;
        $montantFormatted = number_format($facture->montant_reclame, 0, ',', ' ') . ' FCFA';

        // 1. Notifier le prestataire
        if ($prestataire && $prestataire->user) {
            $this->createNotification(
                $prestataire->user->id,
                'Facture rejetée par comptable',
                "Votre facture {$facture->numero_facture} a été rejetée par le comptable : {$facture->motif_rejet_comptable}",
                'error',
                [
                    'facture_id' => $facture->id,
                    'numero_facture' => $facture->numero_facture,
                    'assure_id' => $assure->id,
                    'assure_nom' => $assure->nom . ' ' . $assure->prenoms,
                    'montant_reclame' => $facture->montant_reclame,
                    'montant_formatted' => $montantFormatted,
                    'motif_rejet' => $facture->motif_rejet_comptable,
                    'date_rejet' => $facture->rejet_par_comptable_a->format('d/m/Y à H:i'),
                    'type_notification' => 'facture_rejetee_comptable'
                ]
            );
        }

        // 2. Notifier l'assuré
        if ($assure && $assure->user) {
            $this->createNotification(
                $assure->user->id,
                'Facture rejetée par comptable',
                "Votre facture {$facture->numero_facture} a été rejetée par le comptable : {$facture->motif_rejet_comptable}",
                'error',
                [
                    'facture_id' => $facture->id,
                    'numero_facture' => $facture->numero_facture,
                    'prestataire_id' => $prestataire->id,
                    'prestataire_nom' => $prestataire->raison_sociale,
                    'montant_reclame' => $facture->montant_reclame,
                    'montant_formatted' => $montantFormatted,
                    'motif_rejet' => $facture->motif_rejet_comptable,
                    'date_rejet' => $facture->rejet_par_comptable_a->format('d/m/Y à H:i'),
                    'type_notification' => 'facture_rejetee_comptable_assure'
                ]
            );
        }

        // 3. Notifier le technicien qui a validé
        if ($facture->technicien && $facture->technicien->user) {
            $this->createNotification(
                $facture->technicien->user->id,
                'Facture rejetée par comptable',
                "Facture {$facture->numero_facture} que vous avez validée a été rejetée par le comptable - {$montantFormatted}",
                'warning',
                [
                    'facture_id' => $facture->id,
                    'numero_facture' => $facture->numero_facture,
                    'prestataire_id' => $prestataire->id,
                    'prestataire_nom' => $prestataire->raison_sociale,
                    'assure_id' => $assure->id,
                    'assure_nom' => $assure->nom . ' ' . $assure->prenoms,
                    'montant_reclame' => $facture->montant_reclame,
                    'montant_formatted' => $montantFormatted,
                    'motif_rejet' => $facture->motif_rejet_comptable,
                    'date_rejet' => $facture->rejet_par_comptable_a->format('d/m/Y à H:i'),
                    'type_notification' => 'facture_rejetee_comptable_technicien'
                ]
            );
        }

        // 4. Notifier le médecin qui a validé
        if ($facture->medecin && $facture->medecin->user) {
            $this->createNotification(
                $facture->medecin->user->id,
                'Facture rejetée par comptable',
                "Facture {$facture->numero_facture} que vous avez validée a été rejetée par le comptable - {$montantFormatted}",
                'warning',
                [
                    'facture_id' => $facture->id,
                    'numero_facture' => $facture->numero_facture,
                    'prestataire_id' => $prestataire->id,
                    'prestataire_nom' => $prestataire->raison_sociale,
                    'assure_id' => $assure->id,
                    'assure_nom' => $assure->nom . ' ' . $assure->prenoms,
                    'montant_reclame' => $facture->montant_reclame,
                    'montant_formatted' => $montantFormatted,
                    'motif_rejet' => $facture->motif_rejet_comptable,
                    'date_rejet' => $facture->rejet_par_comptable_a->format('d/m/Y à H:i'),
                    'type_notification' => 'facture_rejetee_comptable_medecin'
                ]
            );
        }
    }

    /**
     * Notifier le prestataire qu'une facture a été remboursée
     *
     * @param \App\Models\Facture $facture La facture remboursée
     * @return void
     */
    public function notifyRemboursement(\App\Models\Facture $facture): void
    {
        $prestataire = $facture->prestataire;
        $assure = $facture->sinistre->assure;
        $montantFormatted = number_format($facture->montant_a_rembourser, 0, ',', ' ') . ' FCFA';

        $this->createNotification(
            $prestataire->user->id,
            'Facture remboursée',
            "Votre facture {$facture->numero_facture} a été remboursée - {$montantFormatted}",
            'success',
            [
                'facture_id' => $facture->id,
                'numero_facture' => $facture->numero_facture,
                'assure_id' => $assure->id,
                'assure_nom' => $assure->nom . ' ' . $assure->prenoms,
                'montant_rembourse' => $facture->montant_a_rembourser,
                'montant_formatted' => $montantFormatted,
                'date_remboursement' => now()->format('d/m/Y à H:i'),
                'type_notification' => 'facture_remboursee'
            ]
        );
    }

    /**
     * Notifier les techniciens, médecins contrôleurs et comptables d'une nouvelle facture
     *
     * @param \App\Models\Facture $facture La nouvelle facture
     * @return void
     */
    public function notifyNouvelleFacture(\App\Models\Facture $facture): void
    {
        // Récupérer tous les techniciens
        $techniciens = User::whereHas('roles', function ($query) {
            $query->where('name', 'technicien');
        })->get();

        // Récupérer tous les médecins contrôleurs
        $medecinsControleurs = User::whereHas('roles', function ($query) {
            $query->where('name', 'medecin_controleur');
        })->get();

        // Récupérer tous les comptables
        $comptables = User::whereHas('roles', function ($query) {
            $query->where('name', 'comptable');
        })->get();

        $prestataire = $facture->prestataire;
        $assure = $facture->sinistre->assure;
        $montantFormatted = number_format($facture->montant_reclame, 0, ',', ' ') . ' FCFA';

        // Notifier les techniciens
        foreach ($techniciens as $technicien) {
            $this->createNotification(
                $technicien->id,
                'Nouvelle facture à valider',
                "Nouvelle facture {$facture->numero_facture} de {$prestataire->raison_sociale} pour {$assure->nom} {$assure->prenoms} - {$montantFormatted}",
                'info',
                [
                    'facture_id' => $facture->id,
                    'numero_facture' => $facture->numero_facture,
                    'prestataire_id' => $prestataire->id,
                    'prestataire_nom' => $prestataire->raison_sociale,
                    'assure_id' => $assure->id,
                    'assure_nom' => $assure->nom . ' ' . $assure->prenoms,
                    'montant_reclame' => $facture->montant_reclame,
                    'montant_formatted' => $montantFormatted,
                    'date_creation' => $facture->created_at->format('d/m/Y à H:i'),
                    'type_notification' => 'nouvelle_facture_technicien'
                ]
            );
        }

        // Notifier les médecins contrôleurs
        foreach ($medecinsControleurs as $medecin) {
            $this->createNotification(
                $medecin->id,
                'Nouvelle facture en attente de validation médicale',
                "Facture {$facture->numero_facture} de {$prestataire->raison_sociale} - {$montantFormatted}",
                'info',
                [
                    'facture_id' => $facture->id,
                    'numero_facture' => $facture->numero_facture,
                    'prestataire_id' => $prestataire->id,
                    'prestataire_nom' => $prestataire->raison_sociale,
                    'assure_id' => $assure->id,
                    'assure_nom' => $assure->nom . ' ' . $assure->prenoms,
                    'montant_reclame' => $facture->montant_reclame,
                    'montant_formatted' => $montantFormatted,
                    'date_creation' => $facture->created_at->format('d/m/Y à H:i'),
                    'type_notification' => 'nouvelle_facture_medecin'
                ]
            );
        }

        // Notifier les comptables
        foreach ($comptables as $comptable) {
            $this->createNotification(
                $comptable->id,
                'Nouvelle facture en attente d\'autorisation',
                "Facture {$facture->numero_facture} de {$prestataire->raison_sociale} - {$montantFormatted}",
                'info',
                [
                    'facture_id' => $facture->id,
                    'numero_facture' => $facture->numero_facture,
                    'prestataire_id' => $prestataire->id,
                    'prestataire_nom' => $prestataire->raison_sociale,
                    'assure_id' => $assure->id,
                    'assure_nom' => $assure->nom . ' ' . $assure->prenoms,
                    'montant_reclame' => $facture->montant_reclame,
                    'montant_formatted' => $montantFormatted,
                    'date_creation' => $facture->created_at->format('d/m/Y à H:i'),
                    'type_notification' => 'nouvelle_facture_comptable'
                ]
            );
        }
    }

}
