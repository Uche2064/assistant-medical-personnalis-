<?php

namespace App\Jobs;

use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $to;
    public string $subject;
    public string $view;
    public array $data;

    public function __construct(string $to, string $subject, string $view, array $data = [])
    {
        $this->to = $to;
        $this->subject = $subject;
        $this->view = $view;
        $this->data = $data;
    }

    public function handle(NotificationService $notificationService)
    {
        $notificationService->sendEmail(
            $this->to,
            $this->subject,
            $this->view,
            $this->data
        );
    }
}
