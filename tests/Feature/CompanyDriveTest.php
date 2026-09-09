<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\KycDocument;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CompanyDriveTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_admin_can_access_and_upload_to_company_private_drive()
    {
        Storage::fake('public');

        $company = Company::create(['name' => 'Drive Test Realty', 'code' => 'DTR', 'slug' => 'drive-test-realty']);
        $adminRole = Role::create(['company_id' => $company->id, 'name' => 'Admin', 'slug' => 'admin']);
        $user = User::create([
            'company_id' => $company->id,
            'role_id' => $adminRole->id,
            'name' => 'Drive Admin',
            'email' => 'driveadmin@test.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get('/documents');
        $response->assertStatus(200);

        $file = UploadedFile::fake()->create('rera_license.pdf', 500, 'application/pdf');

        $uploadResponse = $this->actingAs($user)->post('/documents/kyc', [
            'file_name' => 'Master RERA Approval License 2026',
            'category' => 'Legal & RERA Documents',
            'confidentiality' => 'Confidential (Admins Only)',
            'document_file' => $file,
            'notes' => 'Official RERA Registration License',
        ]);

        $uploadResponse->assertRedirect();
        $this->assertDatabaseHas('kyc_documents', [
            'company_id' => $company->id,
            'document_type' => 'Legal & RERA Documents',
            'document_number' => 'Confidential (Admins Only)',
        ]);
    }
}
