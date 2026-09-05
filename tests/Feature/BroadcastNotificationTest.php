<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BroadcastNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_send_broadcast_push_notification()
    {
        $company = Company::create([
            'name' => 'Broadcast Test Realty',
            'code' => 'BTR',
            'slug' => 'broadcast-realty',
            'status' => 'active',
        ]);

        $adminRole = Role::create([
            'company_id' => $company->id,
            'name' => 'Admin',
            'slug' => 'admin',
        ]);

        $admin = User::create([
            'company_id' => $company->id,
            'role_id' => $adminRole->id,
            'name' => 'SaaS Admin',
            'email' => 'admin@broadcast.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->actingAs($admin)->post(route('notifications.broadcast'), [
            'title' => '🌸 Happy Holi from REOS!',
            'message' => 'Wishing you and your family a colorful, safe & prosperous Holi!',
            'target_audience' => 'all',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }
}
