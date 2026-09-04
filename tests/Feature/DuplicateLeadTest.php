<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Lead;
use App\Services\DuplicateLeadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DuplicateLeadTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_lead_service_detects_matching_phone_or_email(): void
    {
        $company = Company::create(['name' => 'Metro Realty', 'code' => 'MR', 'slug' => 'metro', 'status' => 'active']);

        Lead::create([
            'company_id' => $company->id,
            'lead_code' => 'LD-ORIG',
            'first_name' => 'Rahul',
            'last_name' => 'Sharma',
            'email' => 'rahul@example.com',
            'phone' => '9876543210',
            'alternate_phone' => '9123456780',
            'status' => 'new',
        ]);

        $service = new DuplicateLeadService();

        // Match by main phone
        $matchByPhone = $service->findDuplicate($company->id, '9876543210');
        $this->assertNotNull($matchByPhone);
        $this->assertEquals('LD-ORIG', $matchByPhone->lead_code);

        // Match by alternate phone
        $matchByAlt = $service->findDuplicate($company->id, '9123456780');
        $this->assertNotNull($matchByAlt);

        // Match by email
        $matchByEmail = $service->findDuplicate($company->id, '0000000000', 'rahul@example.com');
        $this->assertNotNull($matchByEmail);

        // Non-matching query
        $noMatch = $service->findDuplicate($company->id, '9999999999', 'unknown@example.com');
        $this->assertNull($noMatch);
    }
}
