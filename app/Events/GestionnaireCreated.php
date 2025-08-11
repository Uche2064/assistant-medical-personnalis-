<?php

namespace App\Events;

use App\Models\Gestionnaire;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GestionnaireCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $gestionnaire;

    public function __construct(Gestionnaire $gestionnaire)
    {
        $this->gestionnaire = $gestionnaire;
    }

    public function broadcastOn()
    {
        return new Channel('gestionnaires');
    }
}
