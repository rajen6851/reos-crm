<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Role;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserSubscriptionLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_manager_cannot_add_executive_when_plan_limit_reached()
    {
        // 1. Create a minimal subscription plan with limit of 1 user (the manager itself)
        $plan = SubscriptionPlan::create([
            'name' => 'Starter Plan',
            'slug' => 'starter-plan',
            'description' => 'Starter',
            'price' => 1000,
            'price_monthly' => 1000,
            'price_yearly' => 10000,
            'max_users' => 1,
            'max_leads' => 100,
            'max_projects' => 1,
            'features' => [],
            'is_active' => true,
        ]);

        // 2. Create Company and Manager (which takes up the 1 user slot)
        $company = Company::create([
            'name' => 'Limit Test Company',
            'email' => 'limit@test.com',
            'phone' => '1234567890',
            'code' => 'LMT',
            'slug' => 'limit-test-company',
            'subscription_plan_id' => $plan->id,
            'subscription_status' => 'active',
            'subscription_ends_at' => now()->addYear(),
        ]);

        $managerRole = Role::create([
            'name' => 'Manager',
            'slug' => 'manager',
            'company_id' => $company->id,
        ]);

        $executiveRole = Role::create([
            'name' => 'Executive',
            'slug' => 'sales_executive',
            'company_id' => $company->id,
        ]);

        $manager = User::factory()->create([
            'company_id' => $company->id,
            'role_id' => $managerRole->id,
            'is_active' => true,
        ]);

        // 3. Attempt to create a new executive via API
        $response = $this->actingAs($manager, 'sanctum')->postJson('/api/manager/team/executives', [
            'name' => 'New Exec',
            'email' => 'exec@test.com',
            'phone' => '0987654321',
            'role' => 'sales_executive',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // 4. Assert API returns 403 Forbidden with Limit Reached message
        $response->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'message' => "SaaS Plan Limit Reached: Your company's subscription allows a maximum of 1 users.",
            ]);
    }
}
