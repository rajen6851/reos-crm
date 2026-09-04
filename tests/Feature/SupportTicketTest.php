<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Role;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportTicketTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'DatabaseSeeder']);
    }

    public function test_user_can_create_support_ticket()
    {
        $company = Company::first();
        $user = User::factory()->create(['company_id' => $company->id]);

        $response = $this->actingAs($user)->post(route('support-tickets.store'), [
            'subject' => 'Issue with Inventory sync',
            'category' => 'Inventory',
            'priority' => 'high',
            'description' => 'Units are showing as hold instead of available',
        ]);

        $this->assertDatabaseHas('support_tickets', [
            'subject' => 'Issue with Inventory sync',
            'company_id' => $company->id,
            'user_id' => $user->id,
            'status' => 'open',
        ]);

        $ticket = SupportTicket::where('subject', 'Issue with Inventory sync')->first();
        $response->assertRedirect(route('support-tickets.show', $ticket->id));
    }

    public function test_user_can_reply_to_support_ticket()
    {
        $company = Company::first();
        $user = User::factory()->create(['company_id' => $company->id]);

        $ticket = SupportTicket::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'subject' => 'Test Query',
            'category' => 'General',
            'priority' => 'medium',
            'description' => 'Test ticket text',
            'status' => 'open',
        ]);

        $response = $this->actingAs($user)->post(route('support-tickets.reply', $ticket->id), [
            'message' => 'Additional clarification on the query',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('support_ticket_replies', [
            'support_ticket_id' => $ticket->id,
            'message' => 'Additional clarification on the query',
        ]);
    }
}
