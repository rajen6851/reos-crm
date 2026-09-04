<?php

namespace Tests\Feature;

use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\ChatParticipant;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatTest extends TestCase
{
    use RefreshDatabase;

    protected User $user1;
    protected User $user2;
    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::create([
            'name' => 'Chat Test Realty',
            'code' => 'CHAT',
            'slug' => 'chat-realty',
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
            'name' => 'Alice Admin',
            'email' => 'alice@chattest.com',
            'password' => bcrypt('password123'),
        ]);

        $this->user2 = User::create([
            'company_id' => $this->company->id,
            'role_id' => $role->id,
            'name' => 'Bob Manager',
            'email' => 'bob@chattest.com',
            'password' => bcrypt('password123'),
        ]);
    }

    public function test_user_can_start_direct_chat_and_send_messages()
    {
        $response = $this->actingAs($this->user1)->postJson(route('chat.direct'), [
            'user_id' => $this->user2->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $chatId = $response->json('chat_id');

        $chat = Chat::find($chatId);
        $this->assertNotNull($chat);
        $this->assertEquals('direct', $chat->type);

        // Send message
        $sendResponse = $this->actingAs($this->user1)->postJson(route('chat.send', $chat->id), [
            'message' => 'Hello Bob! Welcome to REOS Chat.',
        ]);

        $sendResponse->assertStatus(200);
        $sendResponse->assertJson(['success' => true]);

        // Fetch messages as User 2
        $fetchResponse = $this->actingAs($this->user2)->getJson(route('chat.messages', $chat->id));
        $fetchResponse->assertStatus(200);
        $fetchResponse->assertJsonCount(1, 'messages');
        $this->assertEquals('Hello Bob! Welcome to REOS Chat.', $fetchResponse->json('messages.0.message'));
    }

    public function test_user_can_create_group_chat()
    {
        $response = $this->actingAs($this->user1)->postJson(route('chat.group'), [
            'name' => 'Sales Taskforce Group',
            'participant_ids' => [$this->user2->id],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $chatId = $response->json('chat_id');
        $chat = Chat::find($chatId);

        $this->assertEquals('group', $chat->type);
        $this->assertEquals('Sales Taskforce Group', $chat->name);
        $this->assertCount(2, $chat->participants);
    }
}
