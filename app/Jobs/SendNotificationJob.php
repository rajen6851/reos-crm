<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendNotificationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public User $user,
        public string $type,
        public string $title,
        public string $message
    ) {}

    public function handle(NotificationService $notificationService): void
    {
        $notificationService->notify($this->user, $this->type, $this->title, $this->message);
    }
}
