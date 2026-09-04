<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_can_list_and_create_leads(): void
    {
        $company = Company::create(['name' => 'API Realty', 'code' => 'APR', 'slug' => 'api-realty', 'status' => 'active']);

        $user = User::create([
            'company_id' => $company->id,
            'name' => 'API Sales Agent',
            'email' => 'salesagent@reos.com',
            'phone' => '9999900001',
            'password' => bcrypt('password123'),
        ]);

        $token = $user->createToken('test_token')->plainTextToken;

        // Store Lead via API
        $createResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/leads', [
                'first_name' => 'Michael',
                'last_name' => 'Scott',
                'email' => 'mscott@dundermifflin.com',
                'phone' => '9888877776',
                'notes' => 'Looking for commercial office space',
            ]);

        $createResponse->assertStatus(201);
        $createResponse->assertJsonPath('status', 'success');

        // Fetch Leads via API
        $listResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/leads');

        $listResponse->assertStatus(200);
        $this->assertCount(1, $listResponse->json('data'));
    }
}
