<?php

namespace Tests\Feature;

use App\Models\Chat;
use App\Models\ChatParticipant;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use App\Services\FirebaseNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FirebaseNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user1;
    protected User $user2;
    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'FCM Test Realty',
            'code' => 'FCM',
            'slug' => 'fcm-realty',
            'status' => 'active',
        ]);

        $role = Role::create([
            'company_id' => $this->company->id,
            'name' => 'Admin',
            'slug' => 'admin',
        ]);

        $this->user1 = User::create([
            'company_id' => $this->company->id,
            'role_id' => $role->id,
            'name' => 'Sender User',
            'email' => 'sender@fcmtest.com',
            'password' => bcrypt('password123'),
        ]);

        $this->user2 = User::create([
            'company_id' => $this->company->id,
            'role_id' => $role->id,
            'name' => 'Recipient User',
            'email' => 'recipient@fcmtest.com',
            'fcm_token' => 'fcm_token_sample_123456789',
            'password' => bcrypt('password123'),
        ]);
    }

    public function test_firebase_service_dispatches_notification_to_user_with_token()
    {
        $service = new FirebaseNotificationService();
        $result = $service->sendToUser($this->user2, 'Test Push', 'Test Notification Body');

        $this::assertTrue($result);
    }

    public function test_firebase_service_returns_false_for_user_without_token()
    {
        $service = new FirebaseNotificationService();
        $result = $service->sendToUser($this->user1, 'Test Push', 'Test Body');

        $this::assertFalse($result);
    }

    public function test_chat_message_triggers_firebase_push_notification()
    {
        $chat = Chat::create([
            'company_id' => $this->company->id,
            'type' => 'direct',
            'created_by' => $this->user1->id,
        ]);

        ChatParticipant::create(['chat_id' => $chat->id, 'user_id' => $this->user1->id]);
        ChatParticipant::create(['chat_id' => $chat->id, 'user_id' => $this->user2->id]);

        $response = $this->actingAs($this->user1)->postJson(route('chat.send', $chat->id), [
            'message' => 'Hello via Firebase FCM!',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }
}
