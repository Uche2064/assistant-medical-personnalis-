<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NouveauCompteCree implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $userType;
    public $notification;

    public function __construct(User $user, string $userType, array $notification)
    {
        $this->user = $user;
        $this->userType = $userType;
        $this->notification = $notification;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('techniciens');
    }

    public function broadcastAs()
    {
        return 'nouveau.compte';
    }

    public function broadcastWith()
    {
        return [
            'user' => [
                'id' => $this->user->id,
                'email' => $this->user->email,
                'type' => $this->userType,
                'created_at' => $this->user->created_at->format('d/m/Y à H:i')
            ],
            'notification' => $this->notification
        ];
    }
}
