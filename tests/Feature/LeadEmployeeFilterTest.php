<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Lead;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadEmployeeFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_filter_leads_by_assigned_employee()
    {
        $company = Company::create(['name' => 'Emp Filter Realty', 'code' => 'EFR', 'slug' => 'emp-filter-realty']);
        $adminRole = Role::create(['company_id' => $company->id, 'name' => 'Admin', 'slug' => 'admin']);
        $this->attachPermissionsToRole($adminRole, ['manage-leads']);
        
        $salesRole = Role::create(['company_id' => $company->id, 'name' => 'Sales Executive', 'slug' => 'sales_executive']);

        $admin = User::create([
            'company_id' => $company->id,
            'role_id' => $adminRole->id,
            'name' => 'Company Admin',
            'email' => 'adminemp@test.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $emp1 = User::create([
            'company_id' => $company->id,
            'role_id' => $salesRole->id,
            'name' => 'Alice Agent',
            'email' => 'alice@test.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $emp2 = User::create([
            'company_id' => $company->id,
            'role_id' => $salesRole->id,
            'name' => 'Bob Agent',
            'email' => 'bob@test.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $lead1 = Lead::create([
            'company_id' => $company->id,
            'lead_code' => 'LD-EMP1',
            'first_name' => 'Customer',
            'last_name' => 'One',
            'phone' => '9876543210',
            'status' => 'new',
            'assigned_to_user_id' => $emp1->id,
        ]);

        $lead2 = Lead::create([
            'company_id' => $company->id,
            'lead_code' => 'LD-EMP2',
            'first_name' => 'Customer',
            'last_name' => 'Two',
            'phone' => '9876543211',
            'status' => 'new',
            'assigned_to_user_id' => $emp2->id,
        ]);

        $unassignedLead = Lead::create([
            'company_id' => $company->id,
            'lead_code' => 'LD-UNASSIGNED',
            'first_name' => 'Customer',
            'last_name' => 'Three',
            'phone' => '9876543212',
            'status' => 'new',
            'assigned_to_user_id' => null,
        ]);

        // Filter by Emp1 (Alice)
        $resEmp1 = $this->actingAs($admin)->get('/leads?assigned_to_user_id=' . $emp1->id);
        $resEmp1->assertStatus(200);
        $resEmp1->assertSee('Customer One');
        $resEmp1->assertDontSee('Customer Two');

        // Filter by Unassigned
        $resUnassigned = $this->actingAs($admin)->get('/leads?assigned_to_user_id=unassigned');
        $resUnassigned->assertStatus(200);
        $resUnassigned->assertSee('Customer Three');
        $resUnassigned->assertDontSee('Customer One');
    }
}
