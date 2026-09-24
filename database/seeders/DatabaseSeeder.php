<?php

namespace Database\Seeders;

use App\Models\Broker;
use App\Models\BrokerLead;
use App\Models\Company;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\Permission;
use App\Models\Project;
use App\Models\ProjectBuilding;
use App\Models\ProjectFloor;
use App\Models\Role;
use App\Models\SubscriptionPlan;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Subscription Plans
        $basicPlan = SubscriptionPlan::create([
            'name' => 'Starter Plan',
            'slug' => 'starter-plan',
            'price' => 4999.00,
            'billing_cycle' => 'monthly',
            'max_users' => 5,
            'max_projects' => 2,
            'max_leads_per_month' => 500,
            'features' => ['CRM', 'Lead Assignment', 'Basic Reports'],
        ]);

        $growthPlan = SubscriptionPlan::create([
            'name' => 'Growth Enterprise',
            'slug' => 'growth-enterprise',
            'price' => 14999.00,
            'billing_cycle' => 'monthly',
            'max_users' => 25,
            'max_projects' => 10,
            'max_leads_per_month' => 5000,
            'features' => ['CRM', 'Inventory Locking', 'Broker Portal', 'Razorpay Payments', 'WhatsApp Reminders'],
        ]);

        // 2. Tenant Company 1: Apex Realty Infra Pvt Ltd
        $company1 = Company::create([
            'name' => 'Apex Realty Infra Pvt Ltd',
            'code' => 'APEX',
            'slug' => 'apex-realty',
            'logo_path' => '/uploads/company/logo.png',
            'email' => 'contact@apexrealty.com',
            'phone' => '+91 9876543210',
            'address' => 'Suite 402, Financial District, Cyber City, Hyderabad',
            'tax_number' => '36AAACA12341ZV',
            'status' => 'active',
            'subscription_plan_id' => $growthPlan->id,
            'subscription_expires_at' => now()->addYear(),
        ]);

        // 3. Tenant Company 2: Gyansheela Township
        $company2 = Company::create([
            'name' => 'Gyansheela Township',
            'code' => 'GYAN',
            'slug' => 'gyansheela-township',
            'logo_path' => '/uploads/company/gyansheela.png',
            'email' => 'contact@gyansheela.com',
            'phone' => '+91 9855656255',
            'address' => 'Main Bypass Road, Indore, Madhya Pradesh',
            'tax_number' => '23AAACG99881ZV',
            'status' => 'active',
            'subscription_plan_id' => $growthPlan->id,
            'subscription_expires_at' => now()->addYear(),
        ]);

        // 4. Roles Definition for Company 1
        $roleSlugs = [
            'founder' => 'Founder / Director',
            'director' => 'Director',
            'admin' => 'Admin',
            'manager' => 'Manager',
            'sales_executive' => 'Sales Executive',
            'support_team' => 'Support Desk',
            'broker' => 'Broker',
        ];

        $roles1 = [];
        foreach ($roleSlugs as $slug => $name) {
            $roles1[$slug] = Role::create([
                'company_id' => $company1->id,
                'name' => $name,
                'slug' => $slug,
                'description' => "{$name} role for REOS platform",
            ]);
        }

        $roles2 = [];
        foreach ($roleSlugs as $slug => $name) {
            $roles2[$slug] = Role::create([
                'company_id' => $company2->id,
                'name' => $name,
                'slug' => $slug,
                'description' => "{$name} role for REOS platform",
            ]);
        }

        // 4.5. Initialize and Assign Permissions
        $defaultPermissions = [
            ['name' => 'View & Manage Customer Leads', 'slug' => 'manage-leads', 'module' => 'Leads'],
            ['name' => 'Assign Leads to Sales Team', 'slug' => 'assign-leads', 'module' => 'Leads'],
            ['name' => 'Delete / Archive Leads', 'slug' => 'delete-leads', 'module' => 'Leads'],
            ['name' => 'Export Leads Data (Excel/CSV)', 'slug' => 'export-leads', 'module' => 'Leads'],
            ['name' => 'Manage Projects & Buildings', 'slug' => 'manage-projects', 'module' => 'Inventory'],
            ['name' => 'Manage Unit Inventory & Pricing', 'slug' => 'manage-units', 'module' => 'Inventory'],
            ['name' => 'Approve Unit Booking Locks', 'slug' => 'approve-bookings', 'module' => 'Bookings'],
            ['name' => 'Approve Agreement Step Skips', 'slug' => 'approve-agreement-skips', 'module' => 'Bookings'],
            ['name' => 'Manage Broker Commissions', 'slug' => 'manage-commissions', 'module' => 'Finance'],
            ['name' => 'Process & Approve Payouts', 'slug' => 'process-payouts', 'module' => 'Finance'],
            ['name' => 'Manage Team Users & Roles', 'slug' => 'manage-users', 'module' => 'Users'],
            ['name' => 'Access Broker Channel Partner Portal', 'slug' => 'broker-access', 'module' => 'Channel Partners'],
            ['name' => 'View System Reports & Analytics', 'slug' => 'view-reports', 'module' => 'Reports'],
            ['name' => 'Edit Company Settings & Branding', 'slug' => 'company-settings', 'module' => 'Settings'],
            ['name' => 'Manage Digital File Repository', 'slug' => 'manage-documents', 'module' => 'Documents'],
        ];

        $permsMap = [];
        foreach ($defaultPermissions as $perm) {
            $permsMap[$perm['slug']] = Permission::firstOrCreate(['slug' => $perm['slug']], $perm)->id;
        }

        // Map roles to permissions
        $rolePermsMap = [
            'founder' => array_values($permsMap),
            'director' => array_values($permsMap),
            'admin' => array_values($permsMap),
            'manager' => [
                $permsMap['manage-leads'], $permsMap['assign-leads'], $permsMap['approve-bookings'],
                $permsMap['manage-documents'], $permsMap['view-reports']
            ],
            'sales_executive' => [
                $permsMap['manage-leads'], $permsMap['manage-documents']
            ],
            'broker' => [
                $permsMap['broker-access']
            ]
        ];

        foreach (['founder', 'director', 'admin', 'manager', 'sales_executive', 'broker'] as $slug) {
            if (isset($roles1[$slug])) $roles1[$slug]->permissions()->sync($rolePermsMap[$slug]);
            if (isset($roles2[$slug])) $roles2[$slug]->permissions()->sync($rolePermsMap[$slug]);
        }

        // 5. Users
        $defaultPassword = Hash::make('password123');

        // SaaS Master Founder (company_id = NULL)
        $founder = User::create([
            'company_id' => null,
            'role_id' => $roles1['founder']->id,
            'name' => 'Rajesh Sharma (Founder)',
            'email' => 'founder@reos.com',
            'phone' => '9800000001',
            'password' => $defaultPassword,
            'is_super_admin' => true,
        ]);

        $saasSubAdmin = User::updateOrCreate(['email' => 'subadmin@reos.com'], [
            // SaaS users are platform accounts, not members of a tenant company.
            'company_id' => null,
            'role_id' => null,
            'name' => 'Vikram Roy (SaaS Sub-Admin)',
            'email' => 'subadmin@reos.com',
            'phone' => '9800000099',
            'password' => $defaultPassword,
            'is_saas_sub_admin' => true,
            'saas_permissions' => ['onboard_companies', 'manage_subscriptions', 'view_reports'],
        ]);

        // Company 1 Users (Apex Realty Infra Pvt Ltd)
        $director1 = User::create([
            'company_id' => $company1->id,
            'role_id' => $roles1['director']->id,
            'name' => 'Rajeev Malhotra (Director)',
            'email' => 'director@apexrealty.com',
            'phone' => '9800000005',
            'password' => $defaultPassword,
        ]);

        $admin1 = User::create([
            'company_id' => $company1->id,
            'role_id' => $roles1['admin']->id,
            'name' => 'Anil Verma (Admin)',
            'email' => 'admin@apexrealty.com',
            'phone' => '9800000002',
            'password' => $defaultPassword,
        ]);

        // Sales Manager 1
        $manager1 = User::create([
            'company_id' => $company1->id,
            'role_id' => $roles1['manager']->id,
            'name' => 'Priya Nair (Sales Manager 1)',
            'email' => 'manager@apexrealty.com',
            'phone' => '9800000003',
            'password' => $defaultPassword,
        ]);

        // Sales Manager 2
        $manager2 = User::create([
            'company_id' => $company1->id,
            'role_id' => $roles1['manager']->id,
            'name' => 'Rahul Sharma (Sales Manager 2)',
            'email' => 'rahul.manager@apexrealty.com',
            'phone' => '9800000008',
            'password' => $defaultPassword,
        ]);

        // Sales Manager 3
        $manager3 = User::create([
            'company_id' => $company1->id,
            'role_id' => $roles1['manager']->id,
            'name' => 'Anjali Mehta (Sales Manager 3)',
            'email' => 'anjali.manager@apexrealty.com',
            'phone' => '9800000009',
            'password' => $defaultPassword,
        ]);

        // Sales Manager 1 Team (Exec 1 to Exec 4)
        $exec1_1 = User::create([
            'company_id' => $company1->id,
            'role_id' => $roles1['sales_executive']->id,
            'reporting_manager_id' => $manager1->id,
            'name' => 'Vikram Singh (Executive 1)',
            'email' => 'sales@apexrealty.com',
            'phone' => '9800000004',
            'password' => $defaultPassword,
        ]);

        $exec1_2 = User::create([
            'company_id' => $company1->id,
            'role_id' => $roles1['sales_executive']->id,
            'reporting_manager_id' => $manager1->id,
            'name' => 'Neha Gupta (Executive 2)',
            'email' => 'neha.exec@apexrealty.com',
            'phone' => '9800000014',
            'password' => $defaultPassword,
        ]);

        $exec1_3 = User::create([
            'company_id' => $company1->id,
            'role_id' => $roles1['sales_executive']->id,
            'reporting_manager_id' => $manager1->id,
            'name' => 'Rohan Verma (Executive 3)',
            'email' => 'rohan.exec@apexrealty.com',
            'phone' => '9800000015',
            'password' => $defaultPassword,
        ]);

        $exec1_4 = User::create([
            'company_id' => $company1->id,
            'role_id' => $roles1['sales_executive']->id,
            'reporting_manager_id' => $manager1->id,
            'name' => 'Kavita Patel (Executive 4)',
            'email' => 'kavita.exec@apexrealty.com',
            'phone' => '9800000016',
            'password' => $defaultPassword,
        ]);

        // Sales Manager 2 Team (Exec 5 to Exec 7)
        $exec2_1 = User::create([
            'company_id' => $company1->id,
            'role_id' => $roles1['sales_executive']->id,
            'reporting_manager_id' => $manager2->id,
            'name' => 'Amit Kulkarni (Executive 5)',
            'email' => 'amit.exec@apexrealty.com',
            'phone' => '9800000017',
            'password' => $defaultPassword,
        ]);

        $exec2_2 = User::create([
            'company_id' => $company1->id,
            'role_id' => $roles1['sales_executive']->id,
            'reporting_manager_id' => $manager2->id,
            'name' => 'Suresh Reddy (Executive 6)',
            'email' => 'suresh.exec@apexrealty.com',
            'phone' => '9800000018',
            'password' => $defaultPassword,
        ]);

        $exec2_3 = User::create([
            'company_id' => $company1->id,
            'role_id' => $roles1['sales_executive']->id,
            'reporting_manager_id' => $manager2->id,
            'name' => 'Deepika Roy (Executive 7)',
            'email' => 'deepika.exec@apexrealty.com',
            'phone' => '9800000019',
            'password' => $defaultPassword,
        ]);

        // Sales Manager 3 Team (Exec 8 to Exec 10)
        $exec3_1 = User::create([
            'company_id' => $company1->id,
            'role_id' => $roles1['sales_executive']->id,
            'reporting_manager_id' => $manager3->id,
            'name' => 'Pooja Shah (Executive 8)',
            'email' => 'pooja.exec@apexrealty.com',
            'phone' => '9800000020',
            'password' => $defaultPassword,
        ]);

        $exec3_2 = User::create([
            'company_id' => $company1->id,
            'role_id' => $roles1['sales_executive']->id,
            'reporting_manager_id' => $manager3->id,
            'name' => 'Karan Joshi (Executive 9)',
            'email' => 'karan.exec@apexrealty.com',
            'phone' => '9800000021',
            'password' => $defaultPassword,
        ]);

        $exec3_3 = User::create([
            'company_id' => $company1->id,
            'role_id' => $roles1['sales_executive']->id,
            'reporting_manager_id' => $manager3->id,
            'name' => 'Sunil Rao (Executive 10)',
            'email' => 'sunil.exec@apexrealty.com',
            'phone' => '9800000022',
            'password' => $defaultPassword,
        ]);

        $brokerUser = User::create([
            'company_id' => $company1->id,
            'role_id' => $roles1['broker']->id,
            'name' => 'Sunil Realty Services (Broker)',
            'email' => 'broker@apexrealty.com',
            'phone' => '9800000006',
            'password' => $defaultPassword,
        ]);

        $broker = Broker::create([
            'company_id' => $company1->id,
            'user_id' => $brokerUser->id,
            'agency_name' => 'Sunil Channel Partners',
            'broker_code' => 'BRK-9012',
            'phone' => '9800000006',
            'email' => 'broker@apexrealty.com',
            'commission_rate' => 2.50,
            'status' => 'active',
        ]);

        // Company 2 Users (Gyansheela Township)
        $admin2 = User::create([
            'company_id' => $company2->id,
            'role_id' => $roles2['admin']->id,
            'name' => 'Preeteek',
            'email' => 'preeteek@gmail.com',
            'phone' => '9855656255',
            'password' => $defaultPassword,
        ]);

        $managerGyansheela = User::create([
            'company_id' => $company2->id,
            'role_id' => $roles2['manager']->id,
            'name' => 'Krishna (Manager)',
            'email' => 'krishna@gmail.com',
            'phone' => '8888855565',
            'password' => $defaultPassword,
        ]);

        // 6. Projects & Inventory for Company 1 (Apex Realty Infra)
        $project1 = Project::create([
            'company_id' => $company1->id,
            'name' => 'Apex Grand Residency',
            'code' => 'AGR-01',
            'location_address' => 'Gachibowli Ring Road, Hyderabad',
            'city' => 'Hyderabad',
            'state' => 'Telangana',
            'pincode' => '500032',
            'rera_number' => 'P02400009876',
            'project_type' => 'residential',
            'banner_image' => '/uploads/projects/default_project.jpg',
            'amenities' => ['Clubhouse', 'Swimming Pool', 'Gym', 'EV Parking', 'Squash Court'],
            'status' => 'active',
        ]);

        $building1 = ProjectBuilding::create([
            'company_id' => $company1->id,
            'project_id' => $project1->id,
            'name' => 'Tower A (Luxury Block)',
            'code' => 'TWR-A',
            'total_floors' => 10,
            'total_units' => 20,
        ]);

        $floor1 = ProjectFloor::create([
            'company_id' => $company1->id,
            'building_id' => $building1->id,
            'floor_number' => 5,
            'name' => '5th Floor',
            'total_units' => 4,
        ]);

        for ($u = 501; $u <= 504; $u++) {
            Unit::create([
                'company_id' => $company1->id,
                'project_id' => $project1->id,
                'building_id' => $building1->id,
                'floor_id' => $floor1->id,
                'unit_number' => (string) $u,
                'unit_type' => ($u % 2 == 0) ? '3BHK' : '2BHK',
                'carpet_area' => 1350.00,
                'builtup_area' => 1620.00,
                'super_builtup_area' => 1850.00,
                'facing' => 'East',
                'base_price' => 7500000.00,
                'final_price' => 8200000.00,
                'status' => ($u == 501) ? 'booked' : 'available',
            ]);
        }

        // 7. Projects & Inventory for Company 2 (Gyansheela Township -> Subh Angan Indore)
        $projectGyansheela = Project::create([
            'company_id' => $company2->id,
            'name' => 'Subh Angan',
            'code' => 'APR-1',
            'location_address' => 'Main Bypass Road, Indore',
            'city' => 'Indore',
            'state' => 'Madhya Pradesh',
            'pincode' => '452001',
            'rera_number' => 'PHH8273',
            'project_type' => 'residential',
            'banner_image' => '/uploads/projects/default_project.jpg',
            'amenities' => ['Temple', 'Green Gardens', 'Clubhouse', 'Children Play Area'],
            'status' => 'active',
        ]);

        $buildingGyan = ProjectBuilding::create([
            'company_id' => $company2->id,
            'project_id' => $projectGyansheela->id,
            'name' => 'Block 1',
            'code' => 'BLK-1',
            'total_floors' => 5,
            'total_units' => 10,
        ]);

        $floorGyan = ProjectFloor::create([
            'company_id' => $company2->id,
            'building_id' => $buildingGyan->id,
            'floor_number' => 1,
            'name' => '1st Floor',
            'total_units' => 5,
        ]);

        for ($u = 101; $u <= 105; $u++) {
            Unit::create([
                'company_id' => $company2->id,
                'project_id' => $projectGyansheela->id,
                'building_id' => $buildingGyan->id,
                'floor_id' => $floorGyan->id,
                'unit_number' => (string) $u,
                'unit_type' => ($u % 2 == 0) ? '3BHK' : '2BHK',
                'carpet_area' => 1100.00,
                'builtup_area' => 1350.00,
                'super_builtup_area' => 1500.00,
                'facing' => 'East',
                'base_price' => 7800000.00,
                'final_price' => 7800000.00,
                'status' => 'available',
            ]);
        }

        // 8. Lead Sources
        $sourceWeb = LeadSource::create(['company_id' => $company1->id, 'name' => 'Website', 'slug' => 'website']);
        $sourceBroker = LeadSource::create(['company_id' => $company1->id, 'name' => 'Broker Channel', 'slug' => 'broker-channel']);

        // 9. Sample Leads
        $leadDistributionService = new \App\Services\LeadDistributionService();

        Lead::create([
            'company_id' => $company1->id,
            'lead_code' => 'LD-8801',
            'first_name' => 'Amit',
            'last_name' => 'Kulkarni',
            'email' => 'amit.k@gmail.com',
            'phone' => '9988776655',
            'source_id' => $sourceWeb->id,
            'assigned_to_user_id' => $exec1_1->id,
            'assigned_to_manager_id' => $manager1->id,
            'interested_project_id' => $project1->id,
            'status' => 'site_visit',
        ]);

        $brokerLeadObj = Lead::create([
            'company_id' => $company1->id,
            'lead_code' => 'LD-8802',
            'first_name' => 'Suresh',
            'last_name' => 'Reddy',
            'email' => 'suresh.reddy@yahoo.com',
            'phone' => '9123456789',
            'source_id' => $sourceBroker->id,
            'broker_id' => $broker->id,
            'assigned_to_user_id' => $exec1_1->id,
            'assigned_to_manager_id' => $manager1->id,
            'interested_project_id' => $project1->id,
            'status' => 'negotiation',
        ]);

        BrokerLead::create([
            'company_id' => $company1->id,
            'broker_id' => $broker->id,
            'lead_id' => $brokerLeadObj->id,
            'project_id' => $project1->id,
            'submitted_at' => now()->subDays(4),
            'broker_visible_status' => 'Negotiation',
        ]);

        // 10. Generate 10 New Dummy Leads Auto-Distributed between Manager 1 (Priya) and Manager 2 (Rahul)
        $sampleNames = [
            ['Rohan', 'Verma'],
            ['Sneha', 'Kapoor'],
            ['Manish', 'Chawla'],
            ['Pooja', 'Mehta'],
            ['Karan', 'Joshi'],
            ['Divya', 'Saxena'],
            ['Abhinav', 'Singhal'],
            ['Kavita', 'Rao'],
            ['Ravi', 'Tiwari'],
            ['Ananya', 'Deshmukh'],
        ];

        foreach ($sampleNames as $idx => $n) {
            $num = $idx + 1;
            $dLead = Lead::create([
                'company_id' => $company1->id,
                'lead_code' => "LD-AUTO-{$num}",
                'first_name' => $n[0],
                'last_name' => $n[1],
                'email' => strtolower($n[0]) . '.' . strtolower($n[1]) . '@gmail.com',
                'phone' => "981100000{$num}",
                'source_id' => $sourceWeb->id,
                'interested_project_id' => $project1->id,
                'status' => 'new',
            ]);

            // Auto-distribute lead between Manager 1 and Manager 2 via Round-Robin
            $leadDistributionService->distributeNewLead($dLead);
        }

        // 11. Lead Distribution Engine Rules (Default Templates)
        $ruleRoundRobin = \App\Models\DistributionRule::create([
            'company_id' => $company1->id,
            'name' => 'Default Round Robin',
            'priority' => 5,
            'distribution_method' => 'round_robin',
            'fallback_behavior' => 'redistribute',
            'is_active' => true,
            'version' => 1,
        ]);

        $rulePercentage = \App\Models\DistributionRule::create([
            'company_id' => $company1->id,
            'name' => 'Default Percentage Allocation',
            'priority' => 10,
            'distribution_method' => 'percentage',
            'fallback_behavior' => 'redistribute',
            'is_active' => false,
            'version' => 1,
        ]);

        $ruleFixed = \App\Models\DistributionRule::create([
            'company_id' => $company1->id,
            'name' => 'Fixed Quantity (Limit Based)',
            'priority' => 15,
            'distribution_method' => 'fixed_quantity',
            'fallback_behavior' => 'unassigned',
            'is_active' => false,
            'version' => 1,
        ]);

        $rulePerformance = \App\Models\DistributionRule::create([
            'company_id' => $company1->id,
            'name' => 'Performance Based (AI Recommended)',
            'priority' => 20,
            'distribution_method' => 'performance',
            'fallback_behavior' => 'redistribute',
            'is_active' => false,
            'version' => 1,
        ]);

        // Assign members to Round Robin Rule (Active Default)
        \App\Models\DistributionRuleMember::create([
            'distribution_rule_id' => $ruleRoundRobin->id,
            'user_id' => $exec1_1->id,
            'allocation_value' => null,
        ]);

        \App\Models\DistributionRuleMember::create([
            'distribution_rule_id' => $ruleRoundRobin->id,
            'user_id' => $exec1_2->id,
            'allocation_value' => null,
        ]);

        \App\Models\DistributionRuleMember::create([
            'distribution_rule_id' => $ruleRoundRobin->id,
            'user_id' => $exec1_3->id,
            'allocation_value' => null,
        ]);

        // 12. Create a Mock Booking (Pending Approval) for Apex Realty
        $bookedUnit = Unit::where('company_id', $company1->id)->where('unit_number', '501')->first();
        if ($bookedUnit) {
            \App\Models\Booking::create([
                'company_id' => $company1->id,
                'booking_code' => 'BKG-APEX-001',
                'lead_id' => $leadDistributionService ? Lead::where('lead_code', 'LD-8801')->first()->id ?? null : null,
                'customer_name' => 'Amit Kulkarni',
                'customer_email' => 'amit.k@gmail.com',
                'customer_phone' => '9988776655',
                'project_id' => $project1->id,
                'unit_id' => $bookedUnit->id,
                'unit_identifier' => '501',
                'sales_user_id' => $exec1_1->id, // Vikram Singh
                'booking_amount' => 100000.00, // Token amount
                'total_unit_cost' => 8200000.00,
                'booking_date' => now()->subDays(1),
                'status' => 'pending_approval',
                'approval_status' => 'pending',
            ]);
        }
    }
}
