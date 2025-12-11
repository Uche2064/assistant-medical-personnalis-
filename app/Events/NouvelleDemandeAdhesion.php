<?php

namespace App\Events;

use App\Models\DemandeAdhesion;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\BroadcastException;
use Illuminate\Support\Facades\Log;

class NouvelleDemandeAdhesion implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $demande;
    public $notification;

    public function __construct(DemandeAdhesion $demande, array $notification)
    {
        $this->demande = $demande;
        $this->notification = $notification;
    }

    public function broadcastOn()
    {
        $channelName = $this->demande->type_demandeur === 'entreprise' || $this->demande->type_demandeur === 'client'
            ? 'techniciens'
            : 'medecins_controleurs';

        return new PrivateChannel($channelName);
    }

    public function broadcastAs()
    {
        return 'nouvelle.demande.adhésion';
    }

    public function broadcastWith()
    {
        return [
            'demande' => [
                'id' => $this->demande->id,
                'type_demandeur' => $this->demande->type_demandeur,
                'statut' => $this->demande->statut,
                'created_at' => $this->demande->created_at->format('d/m/Y à H:i'),
                'user_email' => $this->demande->user->email
            ],
            'notification' => $this->notification
        ];
    }

    /**
     * Gérer les erreurs de broadcasting
     */
    public function broadcastFailed(\Exception $exception): void
    {
        // Logger l'erreur mais ne pas interrompre l'application
        Log::warning('Erreur de broadcasting pour NouvelleDemandeAdhesion (non bloquante): ' . $exception->getMessage());
    }
}
