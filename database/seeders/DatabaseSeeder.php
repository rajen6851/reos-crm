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

        // Company 1 Users
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

        $manager1 = User::create([
            'company_id' => $company1->id,
            'role_id' => $roles1['manager']->id,
            'name' => 'Priya Nair (Manager)',
            'email' => 'manager@apexrealty.com',
            'phone' => '9800000003',
            'password' => $defaultPassword,
        ]);

        $sales1 = User::create([
            'company_id' => $company1->id,
            'role_id' => $roles1['sales_executive']->id,
            'name' => 'Vikram Singh (Sales Exec)',
            'email' => 'sales@apexrealty.com',
            'phone' => '9800000004',
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

        $manager2 = User::create([
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
        Lead::create([
            'company_id' => $company1->id,
            'lead_code' => 'LD-8801',
            'first_name' => 'Amit',
            'last_name' => 'Kulkarni',
            'email' => 'amit.k@gmail.com',
            'phone' => '9988776655',
            'source_id' => $sourceWeb->id,
            'assigned_to_user_id' => $sales1->id,
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
            'assigned_to_user_id' => $sales1->id,
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
    }
}
