<?php

namespace App\Notifications;

use App\Models\Facture;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NouvelleFactureNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $facture;

    /**
     * Create a new notification instance.
     */
    public function __construct(Facture $facture)
    {
        $this->facture = $facture;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nouvelle facture en attente de validation')
            ->line('Une nouvelle facture a été soumise et nécessite votre attention.')
            ->line('Numéro de facture: ' . $this->facture->numero_facture)
            ->line('Prestataire: ' . $this->facture->prestataire->raison_sociale)
            ->line('Patient: ' . $this->facture->sinistre->assure->nom . ' ' . $this->facture->sinistre->assure->prenoms)
            ->line('Montant réclamé: ' . number_format($this->facture->montant_reclame, 2) . ' FCFA')
            ->action('Voir la facture', url('/admin/factures/' . $this->facture->id))
            ->line('Merci de traiter cette demande dans les meilleurs délais.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'nouvelle_facture',
            'title' => 'Nouvelle facture en attente',
            'message' => 'Le prestataire ' . $this->facture->prestataire->raison_sociale . ' a soumis une nouvelle facture pour validation.',
            'facture_id' => $this->facture->id,
            'numero_facture' => $this->facture->numero_facture,
            'montant_reclame' => $this->facture->montant_reclame,
            'prestataire' => [
                'id' => $this->facture->prestataire->id,
                'raison_sociale' => $this->facture->prestataire->raison_sociale,
                'type_prestataire' => $this->facture->prestataire->type_prestataire,
            ],
            'patient' => [
                'id' => $this->facture->sinistre->assure->id,
                'nom' => $this->facture->sinistre->assure->nom,
                'prenoms' => $this->facture->sinistre->assure->prenoms,
            ],
            'created_at' => now(),
        ];
    }
}