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
            Log::info("[FCM PUSH SKIPPED] User #{$user->id} ({$user->name}) has no FCM device token registered.");
            return false;
        }

        return $this->dispatchFcmMessage($user->fcm_token, $title, $body, $data, $user);
    }

    public function sendToUsers(iterable $users, string $title, string $body, array $data = []): int
    {
        $sentCount = 0;

        foreach ($users as $user) {
            if ($user instanceof User) {
                if (!empty($user->fcm_token)) {
                    if ($this->dispatchFcmMessage($user->fcm_token, $title, $body, $data, $user)) {
                        $sentCount++;
                    }
                } else {
                    Log::info("[FCM PUSH SKIPPED] Recipient User #{$user->id} ({$user->name}) has no FCM token in DB.");
                }
            }
        }

        return $sentCount;
    }

    public function sendChatNotification(User $sender, Chat $chat, string $messageText): int
    {
        // Get all participants except sender
        $recipients = $chat->users->where('id', '!=', $sender->id);

        Log::info(sprintf(
            '[FCM CHAT TRIGGER] Sender #%s (%s) sent chat message in Chat #%s to %d recipient(s)',
            $sender->id,
            $sender->name,
            $chat->id,
            $recipients->count()
        ));

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

    /**
     * Sync chat message to Firebase Realtime Database (RTDB) for instantaneous mobile/web realtime updates
     */
    public function syncChatToRealtimeDb(Chat $chat, $msg, User $sender): void
    {
        $databaseUrl = rtrim((string) config('firebase.database_url', ''), '/');
        if (empty($databaseUrl)) {
            return;
        }

        try {
            $endpoint = "{$databaseUrl}/chats/{$chat->id}/messages/{$msg->id}.json";

            $payload = [
                'id' => (string) $msg->id,
                'chat_id' => (string) $chat->id,
                'sender_id' => (string) $sender->id,
                'sender_name' => $sender->name,
                'message' => $msg->message ?? '',
                'attachment_url' => $msg->attachment_path ? \Illuminate\Support\Facades\Storage::url($msg->attachment_path) : null,
                'timestamp' => now()->timestamp * 1000,
                'created_at' => $msg->created_at ? $msg->created_at->format('h:i A') : now()->format('h:i A'),
            ];

            $url = !empty($this->serverKey) && !str_contains($this->serverKey, 'dummy')
                ? "{$endpoint}?auth={$this->serverKey}"
                : $endpoint;

            $response = Http::timeout(3)->put($url, $payload);

            if ($response->successful()) {
                Log::info("[FIREBASE RTDB SYNC SUCCESS] Chat #{$chat->id} Message #{$msg->id} synced to Realtime DB.");
            } else {
                Log::warning("[FIREBASE RTDB SYNC WARN] Status: {$response->status()} | Body: {$response->body()}");
            }
        } catch (\Throwable $e) {
            Log::warning('[FIREBASE RTDB SYNC ERROR] Exception: ' . $e->getMessage());
        }
    }

    protected function dispatchFcmMessage(string $fcmToken, string $title, string $body, array $data = [], ?User $user = null): bool
    {
        $recipientInfo = $user ? "#{$user->id} ({$user->name})" : "Token: {$fcmToken}";

        if (empty($this->serverKey) || str_contains($this->serverKey, 'dummy')) {
            Log::info("FCM Push Notification Simulated: [{$title}] {$body} -> {$recipientInfo}");
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

            if ($response->successful()) {
                Log::info("[FCM PUSH DISPATCH SUCCESS] Title: '{$title}' -> Sent to {$recipientInfo}");
                return true;
            } else {
                Log::warning("[FCM PUSH DISPATCH FAILED] Status: {$response->status()} | Response: {$response->body()} -> Recipient: {$recipientInfo}");
                return false;
            }
        } catch (\Throwable $e) {
            Log::warning('[FCM PUSH DISPATCH ERROR] Exception: ' . $e->getMessage() . " -> Recipient: {$recipientInfo}");
            return false;
        }
    }
}
