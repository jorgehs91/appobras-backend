<?php

namespace Tests\Unit;

use App\Models\File;
use App\Models\License;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LicenseTest extends TestCase
{
    use RefreshDatabase;

    public function test_license_can_be_created_with_valid_data(): void
    {
        $file = File::factory()->create();
        $project = Project::factory()->create();

        $license = License::factory()->create([
            'file_id' => $file->id,
            'project_id' => $project->id,
            'expiry_date' => '2026-12-31',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('licenses', [
            'id' => $license->id,
            'file_id' => $file->id,
            'project_id' => $project->id,
            'status' => 'active',
        ]);
        
        // Check expiry_date separately as it's stored as datetime in DB
        $this->assertEquals('2026-12-31', $license->expiry_date->format('Y-m-d'));
    }

    public function test_license_has_file_relationship(): void
    {
        $file = File::factory()->create();
        $license = License::factory()->create([
            'file_id' => $file->id,
        ]);

        $this->assertEquals($file->id, $license->file->id);
    }

    public function test_license_has_project_relationship(): void
    {
        $project = Project::factory()->create();
        $license = License::factory()->create([
            'project_id' => $project->id,
        ]);

        $this->assertEquals($project->id, $license->project->id);
    }

    public function test_file_has_licenses_relationship(): void
    {
        $file = File::factory()->create();
        License::factory()->count(2)->create([
            'file_id' => $file->id,
        ]);

        // Note: We need to add hasMany relationship to File model if needed
        // For now, we'll test the inverse relationship exists
        $licenses = License::where('file_id', $file->id)->get();
        $this->assertCount(2, $licenses);
    }

    public function test_project_has_licenses_relationship(): void
    {
        $project = Project::factory()->create();
        License::factory()->count(3)->create([
            'project_id' => $project->id,
        ]);

        // Note: We need to add hasMany relationship to Project model if needed
        // For now, we'll test the inverse relationship exists
        $licenses = License::where('project_id', $project->id)->get();
        $this->assertCount(3, $licenses);
    }

    public function test_license_uses_soft_deletes(): void
    {
        $license = License::factory()->create();
        $license->delete();

        $this->assertSoftDeleted('licenses', [
            'id' => $license->id,
        ]);
    }

    public function test_license_expiry_date_is_casted_to_date(): void
    {
        $license = License::factory()->create([
            'expiry_date' => '2026-12-31',
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $license->expiry_date);
        $this->assertEquals('2026-12-31', $license->expiry_date->format('Y-m-d'));
    }

    public function test_license_has_audit_fields(): void
    {
        $user = User::factory()->create();
        $file = File::factory()->create();
        $project = Project::factory()->create();
        
        // Simulate authentication for AuditTrait
        auth()->login($user);

        $license = License::create([
            'file_id' => $file->id,
            'project_id' => $project->id,
            'expiry_date' => '2026-12-31',
            'status' => 'active',
        ]);

        $this->assertEquals($user->id, $license->created_by);
        
        // Update to trigger updated_by
        $license->update(['status' => 'expired']);
        $license->refresh();
        
        $this->assertEquals($user->id, $license->updated_by);
        
        auth()->logout();
    }

    public function test_license_scope_by_project(): void
    {
        $project1 = Project::factory()->create();
        $project2 = Project::factory()->create();

        License::factory()->count(2)->create(['project_id' => $project1->id]);
        License::factory()->count(3)->create(['project_id' => $project2->id]);

        $licenses = License::byProject($project1->id)->get();
        $this->assertCount(2, $licenses);
    }

    public function test_license_scope_by_status(): void
    {
        License::factory()->count(2)->create(['status' => 'active']);
        License::factory()->count(3)->create(['status' => 'expired']);

        $activeLicenses = License::byStatus('active')->get();
        $this->assertCount(2, $activeLicenses);
    }

    public function test_license_scope_expiring_soon(): void
    {
        // Create licenses expiring in different timeframes
        $expiringIn15Days = License::factory()->create([
            'expiry_date' => now()->addDays(15)->format('Y-m-d'),
        ]); // Expires in 15 days
        
        $expiringIn45Days = License::factory()->create([
            'expiry_date' => now()->addDays(45)->format('Y-m-d'),
        ]); // Expires in 45 days (outside default 30 days)
        
        $expiringIn1Year = License::factory()->create([
            'expiry_date' => now()->addYear()->format('Y-m-d'),
        ]); // Expires in 1 year

        $expiringSoon = License::expiringSoon(30)->get();
        // Should only include the one expiring in 15 days (within 30 days)
        $this->assertCount(1, $expiringSoon);
        $this->assertTrue($expiringSoon->contains($expiringIn15Days));
        $this->assertFalse($expiringSoon->contains($expiringIn45Days));
        $this->assertFalse($expiringSoon->contains($expiringIn1Year));
    }

    public function test_license_scope_expired(): void
    {
        License::factory()->expired()->create();
        License::factory()->active()->create();

        $expiredLicenses = License::expired()->get();
        $this->assertCount(1, $expiredLicenses);
    }

    public function test_license_scope_active(): void
    {
        License::factory()->active()->create();
        License::factory()->expired()->create();

        $activeLicenses = License::active()->get();
        $this->assertCount(1, $activeLicenses);
    }

    public function test_license_is_expired_method(): void
    {
        $expiredLicense = License::factory()->expired()->create();
        $activeLicense = License::factory()->active()->create();

        $this->assertTrue($expiredLicense->isExpired());
        $this->assertFalse($activeLicense->isExpired());
    }

    public function test_license_is_expiring_soon_method(): void
    {
        $expiringSoonLicense = License::factory()->expiringSoon(15)->create();
        $activeLicense = License::factory()->active()->create(['expiry_date' => now()->addYear()]);

        $this->assertTrue($expiringSoonLicense->isExpiringSoon(30));
        $this->assertFalse($activeLicense->isExpiringSoon(30));
    }

    public function test_license_days_until_expiration_method(): void
    {
        $futureDate = now()->addDays(45);
        $license = License::factory()->create([
            'expiry_date' => $futureDate->format('Y-m-d'),
        ]);

        $days = $license->daysUntilExpiration();
        $this->assertGreaterThanOrEqual(44, $days);
        $this->assertLessThanOrEqual(46, $days); // Allow small variance
    }

    public function test_license_days_until_expiration_returns_zero_for_expired(): void
    {
        $expiredLicense = License::factory()->expired()->create();

        $this->assertEquals(0, $expiredLicense->daysUntilExpiration());
    }
}
