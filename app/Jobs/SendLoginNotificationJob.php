<?php
namespace App\Jobs;

use App\Enums\EmailType;
use App\Models\User;
use App\Services\NotificationService; // si tu as un service
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendLoginNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function handle(NotificationService $notificationService): void
    {
        $notificationService->sendEmail(
            $this->user->email,
            'Connexion Réussie',
            EmailType::LOGIN->value,
            [
                'user' => $this->user,
                'ip_address' => request()->ip(),
                'user_agent' => request()->header('User-Agent'),
            ]
        );
    }
}
