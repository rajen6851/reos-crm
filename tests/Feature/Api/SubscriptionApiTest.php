<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\Role;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionApiTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected User $adminUser;
    protected SubscriptionPlan $starterPlan;
    protected SubscriptionPlan $growthPlan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->starterPlan = SubscriptionPlan::create([
            'name' => 'Starter Plan',
            'slug' => 'starter-plan',
            'price' => 4999.00,
            'billing_cycle' => 'monthly',
            'max_users' => 3,
            'max_projects' => 1,
            'max_leads_per_month' => 10,
            'features' => ['CRM', 'Basic Reports'],
            'is_active' => true,
        ]);

        $this->growthPlan = SubscriptionPlan::create([
            'name' => 'Growth Enterprise',
            'slug' => 'growth-enterprise',
            'price' => 14999.00,
            'billing_cycle' => 'monthly',
            'max_users' => 20,
            'max_projects' => 10,
            'max_leads_per_month' => 500,
            'features' => ['CRM', 'Broker Portal', 'Inventory Locking', 'WhatsApp Reminders'],
            'is_active' => true,
        ]);

        $this->company = Company::create([
            'name' => 'Subscription Test Company',
            'code' => 'STC',
            'slug' => 'sub-test-co',
            'status' => 'active',
            'subscription_plan_id' => $this->starterPlan->id,
            'subscription_expires_at' => now()->addDays(30),
        ]);

        $adminRole = Role::create([
            'company_id' => $this->company->id,
            'name' => 'Company Admin',
            'slug' => 'admin',
        ]);

        $this->adminUser = User::factory()->create([
            'company_id' => $this->company->id,
            'role_id' => $adminRole->id,
        ]);
    }

    public function test_can_list_subscription_plans()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/subscription/plans');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(2, 'data');
    }

    public function test_can_fetch_company_subscription_status_and_usage()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/subscription/status');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('subscription.company_name', 'Subscription Test Company')
            ->assertJsonPath('subscription.plan.name', 'Starter Plan')
            ->assertJsonPath('subscription.is_subscription_active', true);
    }

    public function test_admin_can_upgrade_subscription_plan()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/subscription/subscribe', [
                'plan_id' => $this->growthPlan->id,
                'payment_gateway' => 'razorpay',
                'transaction_reference' => 'TXN-RAZOR-998877',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('company_summary.plan.name', 'Growth Enterprise');

        $this->assertDatabaseHas('companies', [
            'id' => $this->company->id,
            'subscription_plan_id' => $this->growthPlan->id,
        ]);

        $this->assertDatabaseHas('subscriptions', [
            'company_id' => $this->company->id,
            'subscription_plan_id' => $this->growthPlan->id,
            'transaction_reference' => 'TXN-RAZOR-998877',
        ]);
    }

    public function test_admin_can_renew_subscription()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/subscription/renew', [
                'payment_gateway' => 'razorpay',
                'transaction_reference' => 'TXN-RENEW-12345',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('subscription.status', 'active');
    }

    public function test_subscription_entitlement_checks()
    {
        $this->assertTrue($this->company->isSubscriptionActive());
        $this->assertTrue($this->company->hasFeature('CRM'));
        $this->assertFalse($this->company->hasFeature('WhatsApp Reminders'));

        $this->assertTrue($this->company->canAddUser());
        $this->assertTrue($this->company->canAddProject());
        $this->assertTrue($this->company->canAddLeadMonthly());
    }
}
