<?php

namespace App\Services;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseNotificationService
{
    protected string $serverKey;

    public function __construct()
    {
        $this->serverKey = config('firebase.server_key', '');
    }

    public function sendToUser(User $user, string $title, string $body, array $data = []): bool
    {
        if (empty($user->fcm_token)) {
            return false;
        }

        return $this->dispatchFcmMessage($user->fcm_token, $title, $body, $data);
    }

    public function sendToUsers(iterable $users, string $title, string $body, array $data = []): int
    {
        $sentCount = 0;

        foreach ($users as $user) {
            if ($user instanceof User && !empty($user->fcm_token)) {
                if ($this->dispatchFcmMessage($user->fcm_token, $title, $body, $data)) {
                    $sentCount++;
                }
            }
        }

        return $sentCount;
    }

    public function sendChatNotification(User $sender, Chat $chat, string $messageText): int
    {
        // Get all participants except sender
        $recipients = $chat->users->where('id', '!=', $sender->id);

        $title = $chat->type === 'group'
            ? "👥 {$chat->name}: {$sender->name}"
            : "💬 {$sender->name}";

        $body = mb_strimwidth($messageText, 0, 100, '...');

        $data = [
            'type' => 'chat_message',
            'chat_id' => (string) $chat->id,
            'sender_id' => (string) $sender->id,
            'click_action' => route('chat.index'),
        ];

        return $this->sendToUsers($recipients, $title, $body, $data);
    }

    protected function dispatchFcmMessage(string $fcmToken, string $title, string $body, array $data = []): bool
    {
        if (empty($this->serverKey) || str_contains($this->serverKey, 'dummy')) {
            Log::info("FCM Push Notification Simulated: [{$title}] {$body} -> Token: {$fcmToken}");
            return true;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->serverKey,
                'Content-Type' => 'application/json',
            ])->timeout(5)->post('https://fcm.googleapis.com/fcm/send', [
                'to' => $fcmToken,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'sound' => 'default',
                    'icon' => asset('images/logo.jpg'),
                ],
                'data' => array_merge($data, [
                    'title' => $title,
                    'body' => $body,
                ]),
                'priority' => 'high',
            ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::warning('FCM Push Notification Dispatch Warning: ' . $e->getMessage());
            return false;
        }
    }
}
