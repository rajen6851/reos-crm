<?php

use App\Models\Booking;
use App\Models\Broker;
use App\Models\BrokerLead;
use App\Models\Call;
use App\Models\Company;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\Project;
use App\Models\ProjectBuilding;
use App\Models\ProjectFloor;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('reos:seed-rich-data', function () {
    $company = Company::where('slug', 'apex-realty')->first() ?? Company::first();
    $gyanCompany = Company::where('slug', 'gyansheela-township')->first();

    if (!$company) {
        $this->error('No company found.');
        return;
    }

    $salesUser = User::where('company_id', $company->id)
        ->whereHas('role', function ($q) {
            $q->where('slug', 'sales_executive');
        })
        ->first();

    $broker = Broker::where('company_id', $company->id)->first();

    $sourceWeb = LeadSource::where('company_id', $company->id)->where('slug', 'website')->first()
        ?? LeadSource::create(['company_id' => $company->id, 'name' => 'Website', 'slug' => 'website']);

    $sourceBroker = LeadSource::where('company_id', $company->id)->where('slug', 'broker-channel')->first()
        ?? LeadSource::create(['company_id' => $company->id, 'name' => 'Broker Channel', 'slug' => 'broker-channel']);

    $sourceWalkin = LeadSource::where('company_id', $company->id)->where('slug', 'direct-walkin')->first()
        ?? LeadSource::create(['company_id' => $company->id, 'name' => 'Direct Walk-in', 'slug' => 'direct-walkin']);

    // 1. Create Second Project for Apex: Apex Imperial Towers
    $project2 = Project::firstOrCreate(
        ['company_id' => $company->id, 'code' => 'AIT-02'],
        [
            'name' => 'Apex Imperial Towers',
            'location_address' => 'HITECH City Main Road, Madhapur, Hyderabad',
            'city' => 'Hyderabad',
            'state' => 'Telangana',
            'pincode' => '500081',
            'rera_number' => 'P02400008899',
            'project_type' => 'residential',
            'banner_image' => '/uploads/projects/default_project.jpg',
            'amenities' => ['Infinity Pool', 'Sky Lounge', 'Clubhouse', 'EV Charging', 'Tennis Court'],
            'status' => 'active',
        ]
    );

    // 2. Ensure Gyansheela Township -> Subh Angan (Indore) Project exists
    if ($gyanCompany) {
        $subhAngan = Project::firstOrCreate(
            ['company_id' => $gyanCompany->id, 'code' => 'APR-1'],
            [
                'name' => 'Subh Angan',
                'location_address' => 'Main Bypass Road, Indore',
                'city' => 'Indore',
                'state' => 'Madhya Pradesh',
                'pincode' => '452001',
                'rera_number' => 'PHH8273',
                'project_type' => 'residential',
                'banner_image' => '/uploads/projects/default_project.jpg',
                'amenities' => ['Temple', 'Green Gardens', 'Clubhouse', 'Children Play Area'],
                'status' => 'active',
            ]
        );
    }

    $project1 = Project::where('company_id', $company->id)->first();

    // 3. Dummy Sample Leads
    $sampleLeadsData = [
        [
            'first_name' => 'Rahul', 'last_name' => 'Sharma', 'email' => 'rahul.s@gmail.com', 'phone' => '9811223344',
            'status' => 'new', 'source_id' => $sourceWeb->id, 'broker_id' => null, 'project_id' => $project1->id,
            'notes' => 'Looking for 3BHK unit under 85 Lakhs.',
        ],
        [
            'first_name' => 'Pooja', 'last_name' => 'Malhotra', 'email' => 'pooja.m@yahoo.com', 'phone' => '9822334455',
            'status' => 'contacted', 'source_id' => $sourceWalkin->id, 'broker_id' => null, 'project_id' => $project2->id,
            'notes' => 'Contacted via walk-in visit. Interested in HITECH City location.',
        ],
        [
            'first_name' => 'Sneha', 'last_name' => 'Kapoor', 'email' => 'sneha.k@outlook.com', 'phone' => '9844556677',
            'status' => 'site_visit', 'source_id' => $sourceBroker->id, 'broker_id' => $broker?->id, 'project_id' => $project2->id,
            'notes' => 'Site visit conducted. Loved balcony view.',
        ],
    ];

    foreach ($sampleLeadsData as $ld) {
        $lead = Lead::firstOrCreate(
            ['company_id' => $company->id, 'phone' => $ld['phone']],
            [
                'lead_code' => 'LD-' . rand(8000, 9999),
                'first_name' => $ld['first_name'],
                'last_name' => $ld['last_name'],
                'email' => $ld['email'],
                'source_id' => $ld['source_id'],
                'broker_id' => $ld['broker_id'],
                'assigned_to_user_id' => $salesUser?->id,
                'interested_project_id' => $ld['project_id'],
                'status' => $ld['status'],
                'notes' => $ld['notes'],
            ]
        );

        if ($ld['broker_id']) {
            BrokerLead::firstOrCreate(
                ['company_id' => $company->id, 'lead_id' => $lead->id],
                [
                    'broker_id' => $ld['broker_id'],
                    'project_id' => $ld['project_id'],
                    'submitted_at' => now()->subDays(rand(1, 5)),
                    'broker_visible_status' => ucwords(str_replace('_', ' ', $ld['status'])),
                ]
            );
        }
    }

    $this->info('Subh Angan (Gyansheela Township Indore) and rich sample data seeded successfully!');
})->purpose('Seed Gyansheela Subh Angan and Rich Sample Data');
