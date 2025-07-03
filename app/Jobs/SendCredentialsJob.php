<?php
namespace App\Jobs;

use App\Enums\EmailType;
use App\Models\User;
use App\Services\NotificationService; // si tu as un service
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendCredentialsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected User $user;
    protected String $plainPassword;

    public function __construct(User $user, $plainPassword)
    {
        $this->user = $user;
        $this->plainPassword = $plainPassword;
    }

    public function handle(NotificationService $notificationService): void
    {
        try {
            $notificationService->sendCredentials(
                $this->user,
                $this->plainPassword
            );
            Log::info("Mail envoyé avec succès");

        } catch (Exception $e) {
            Log::error("Erreur lors de l'envoi du mail : " . $e->getMessage());
        }
    }
}
