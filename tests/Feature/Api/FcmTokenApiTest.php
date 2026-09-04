<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FcmTokenApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_fcm_token()
    {
        $company = Company::create(['name' => 'Test Company', 'slug' => 'test-co', 'code' => 'TC']);
        $user = User::factory()->create([
            'company_id' => $company->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/fcm-token', [
                'fcm_token' => 'sample_fcm_token_xyz_123456',
                'device_type' => 'android',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'FCM Push Notification token registered successfully.',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'fcm_token' => 'sample_fcm_token_xyz_123456',
            'device_type' => 'android',
        ]);
    }

    public function test_user_can_remove_fcm_token()
    {
        $company = Company::create(['name' => 'Test Company', 'slug' => 'test-co', 'code' => 'TC']);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'fcm_token' => 'sample_token',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/fcm-token');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'FCM Push Notification token removed successfully.',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'fcm_token' => null,
        ]);
    }
}
