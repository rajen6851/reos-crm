<?php

namespace Tests\Feature;

use App\Mail\UserWelcomeCredentialsMail;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class UserWelcomeCredentialsMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_welcome_email_is_sent_when_admin_creates_new_staff_member()
    {
        Mail::fake();

        $company = Company::create([
            'name' => 'Mail Test Realty',
            'code' => 'MTR',
            'slug' => 'mail-test-realty',
            'status' => 'active',
        ]);

        $adminRole = Role::create([
            'company_id' => $company->id,
            'name' => 'Admin',
            'slug' => 'admin',
        ]);

        $salesRole = Role::create([
            'company_id' => $company->id,
            'name' => 'Sales Executive',
            'slug' => 'sales_executive',
        ]);

        $admin = User::create([
            'company_id' => $company->id,
            'role_id' => $adminRole->id,
            'name' => 'Admin User',
            'email' => 'admin@mailtest.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'New Staff User',
            'email' => 'newstaff@mailtest.com',
            'phone' => '9898989898',
            'role_id' => $salesRole->id,
            'password' => 'secret12345',
            'branch' => 'Head Office',
            'department' => 'Sales',
            'designation' => 'Executive',
        ]);

        $response->assertRedirect(route('users.index'));

        Mail::assertSent(UserWelcomeCredentialsMail::class, function ($mail) {
            return $mail->hasTo('newstaff@mailtest.com') && $mail->rawPassword === 'secret12345';
        });
    }
}
