<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_user_can_login_and_receive_sanctum_token(): void
    {
        $company = Company::create(['name' => 'API Realty', 'code' => 'APR', 'slug' => 'api-realty', 'status' => 'active']);

        $user = User::create([
            'company_id' => $company->id,
            'name' => 'API Agent',
            'email' => 'apiagent@reos.com',
            'phone' => '9999900000',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'apiagent@reos.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['status', 'token', 'user']);
    }

    public function test_authenticated_user_can_fetch_their_profile(): void
    {
        $company = Company::create(['name' => 'API Realty', 'code' => 'APR', 'slug' => 'api-realty', 'status' => 'active']);

        $user = User::create([
            'company_id' => $company->id,
            'name' => 'API Agent',
            'email' => 'apiagent@reos.com',
            'phone' => '9999900000',
            'password' => bcrypt('password123'),
        ]);

        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/me');

        $response->assertStatus(200);
        $response->assertJsonPath('user.email', 'apiagent@reos.com');
    }
}
