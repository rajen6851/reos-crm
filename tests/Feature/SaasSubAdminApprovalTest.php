<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\SaasApprovalRequest;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaasSubAdminApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_saas_founder_can_create_sub_admin_with_permissions()
    {
        $founder = User::factory()->create([
            'is_super_admin' => true,
        ]);

        $response = $this->actingAs($founder)->post(route('admin.sub-admins.store'), [
            'name' => 'SubAdmin Test',
            'email' => 'subadmin@reos.in',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'saas_permissions' => ['view_companies', 'delete_companies'],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'email' => 'subadmin@reos.in',
            'is_saas_sub_admin' => true,
        ]);

        $subAdmin = User::where('email', 'subadmin@reos.in')->first();
        $this->assertTrue($subAdmin->hasSaaSPermission('view_companies'));
        $this->assertTrue($subAdmin->hasSaaSPermission('delete_companies'));
        $this->assertFalse($subAdmin->hasSaaSPermission('manage_subadmins'));
    }

    public function test_sub_admin_delete_company_creates_approval_request()
    {
        $founder = User::factory()->create([
            'is_super_admin' => true,
        ]);

        $subAdmin = User::factory()->create([
            'is_super_admin' => false,
            'is_saas_sub_admin' => true,
            'saas_permissions' => ['delete_companies'],
        ]);

        $company = Company::create([
            'name' => 'Test Builder Co',
            'code' => 'TBC01',
            'slug' => 'test-builder-co',
            'email' => 'tbc@example.com',
            'phone' => '9876543210',
            'status' => 'active',
        ]);

        $response = $this->actingAs($subAdmin)->delete(route('admin.companies.destroy', $company), [
            'reason' => 'Company inactive for long time',
        ]);

        $response->assertRedirect();
        // Company should NOT be deleted yet
        $this->assertDatabaseHas('companies', ['id' => $company->id]);

        // Approval request should be created
        $this->assertDatabaseHas('saas_approval_requests', [
            'requested_by_user_id' => $subAdmin->id,
            'action_type' => 'delete_company',
            'target_id' => $company->id,
            'status' => 'pending',
        ]);

        $approvalReq = SaasApprovalRequest::where('target_id', $company->id)->first();

        // Founder approves the request
        $approveResponse = $this->actingAs($founder)->post(route('admin.saas-approvals.approve', $approvalReq), [
            'reviewer_notes' => 'Approved company removal',
        ]);

        $approveResponse->assertRedirect();

        // Company should now be deleted
        $this->assertDatabaseMissing('companies', ['id' => $company->id]);
        $this->assertDatabaseHas('saas_approval_requests', [
            'id' => $approvalReq->id,
            'status' => 'approved',
        ]);
    }
}
