<?php

namespace Tests\Feature;

use App\Enums\SystemRole;
use App\Models\Company;
use App\Models\File;
use App\Models\License;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LicenseControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create roles using enum values
        Role::create(['name' => SystemRole::Financeiro->value, 'guard_name' => 'sanctum']);
        Role::create(['name' => SystemRole::AdminObra->value, 'guard_name' => 'sanctum']);
        Role::create(['name' => SystemRole::Engenheiro->value, 'guard_name' => 'sanctum']);
    }

    public function test_listar_licenses_requer_permissao(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/licenses', [
            'X-Company-Id' => $company->id,
        ])
            ->assertStatus(403);
    }

    public function test_listar_licenses_com_role_financeiro(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $user->assignRole(SystemRole::Financeiro->value);
        $project = Project::factory()->create(['company_id' => $company->id]);

        $license1 = License::factory()->create([
            'project_id' => $project->id,
            'expiry_date' => Carbon::now()->addDays(60),
        ]);

        $license2 = License::factory()->create([
            'project_id' => $project->id,
            'expiry_date' => Carbon::now()->addDays(90),
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/licenses', [
            'X-Company-Id' => $company->id,
        ])
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $ids = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertContains($license1->id, $ids);
        $this->assertContains($license2->id, $ids);
    }

    public function test_filtrar_licenses_por_project_id(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $user->assignRole(SystemRole::Financeiro->value);
        $project1 = Project::factory()->create(['company_id' => $company->id]);
        $project2 = Project::factory()->create(['company_id' => $company->id]);

        License::factory()->create([
            'project_id' => $project1->id,
        ]);

        License::factory()->create([
            'project_id' => $project2->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/licenses?project_id={$project1->id}", [
            'X-Company-Id' => $company->id,
        ])
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->assertEquals($project1->id, $response->json('data.0.project_id'));
    }

    public function test_filtrar_licenses_por_status(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $user->assignRole(SystemRole::Financeiro->value);
        $project = Project::factory()->create(['company_id' => $company->id]);

        License::factory()->create([
            'project_id' => $project->id,
            'status' => 'active',
        ]);

        License::factory()->create([
            'project_id' => $project->id,
            'status' => 'expired',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/licenses?status=active', [
            'X-Company-Id' => $company->id,
        ])
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->assertEquals('active', $response->json('data.0.status'));
    }

    public function test_filtrar_licenses_por_expiring_soon(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $user->assignRole(SystemRole::Financeiro->value);
        $project = Project::factory()->create(['company_id' => $company->id]);

        // Create license expiring within 30 days (default threshold)
        $expiringLicense = License::factory()->create([
            'project_id' => $project->id,
            'expiry_date' => Carbon::now()->addDays(20)->format('Y-m-d'),
        ]);

        License::factory()->create([
            'project_id' => $project->id,
            'expiry_date' => Carbon::now()->addDays(60)->format('Y-m-d'),
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/licenses?expiring_soon=true', [
            'X-Company-Id' => $company->id,
        ])
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->assertEquals($expiringLicense->id, $response->json('data.0.id'));
    }

    public function test_listar_licenses_expiring_endpoint(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $user->assignRole(SystemRole::Financeiro->value);
        $project = Project::factory()->create(['company_id' => $company->id]);

        $expiringLicense = License::factory()->expiringSoon(20)->create([
            'project_id' => $project->id,
        ]);

        License::factory()->create([
            'project_id' => $project->id,
            'expiry_date' => Carbon::now()->addDays(60),
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/licenses/expiring', [
            'X-Company-Id' => $company->id,
        ])
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->assertEquals($expiringLicense->id, $response->json('data.0.id'));
    }

    public function test_criar_license_com_dados_validos(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $user->assignRole(SystemRole::Financeiro->value);
        $project = Project::factory()->create(['company_id' => $company->id]);
        $file = File::factory()->create();

        Sanctum::actingAs($user);

        $expiryDate = Carbon::now()->addDays(90)->format('Y-m-d');

        $response = $this->postJson('/api/v1/licenses', [
            'file_id' => $file->id,
            'project_id' => $project->id,
            'expiry_date' => $expiryDate,
            'status' => 'active',
            'notes' => 'Licença de alvará de construção',
        ], [
            'X-Company-Id' => $company->id,
        ])
            ->assertStatus(201)
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.notes', 'Licença de alvará de construção');

        $this->assertDatabaseHas('licenses', [
            'file_id' => $file->id,
            'project_id' => $project->id,
            'status' => 'active',
            'created_by' => $user->id,
        ]);

        // Verify expiry_date separately using whereDate
        $license = DB::table('licenses')
            ->where('file_id', $file->id)
            ->where('project_id', $project->id)
            ->whereDate('expiry_date', $expiryDate)
            ->where('status', 'active')
            ->where('created_by', $user->id)
            ->first();

        $this->assertNotNull($license, 'License with correct expiry_date not found');
    }

    public function test_criar_license_requer_permissao(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        $project = Project::factory()->create(['company_id' => $company->id]);
        $file = File::factory()->create();

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/licenses', [
            'file_id' => $file->id,
            'project_id' => $project->id,
            'expiry_date' => Carbon::now()->addDays(90)->format('Y-m-d'),
        ], [
            'X-Company-Id' => $company->id,
        ])
            ->assertStatus(403);
    }

    public function test_criar_license_com_projeto_de_outra_empresa_falha(): void
    {
        $user = User::factory()->create();
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();
        $user->companies()->attach($company1->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company1->id);
        $user->assignRole(SystemRole::Financeiro->value);
        $project = Project::factory()->create(['company_id' => $company2->id]);
        $file = File::factory()->create();

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/licenses', [
            'file_id' => $file->id,
            'project_id' => $project->id,
            'expiry_date' => Carbon::now()->addDays(90)->format('Y-m-d'),
        ], [
            'X-Company-Id' => $company1->id,
        ])
            ->assertStatus(403);
    }

    public function test_visualizar_license_especifica(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $user->assignRole(SystemRole::Financeiro->value);
        $project = Project::factory()->create(['company_id' => $company->id]);

        $license = License::factory()->create([
            'project_id' => $project->id,
            'expiry_date' => Carbon::now()->addDays(90),
        ]);

        Sanctum::actingAs($user);

        $this->getJson("/api/v1/licenses/{$license->id}", [
            'X-Company-Id' => $company->id,
        ])
            ->assertOk()
            ->assertJsonPath('data.id', $license->id)
            ->assertJsonPath('data.project_id', $project->id);
    }

    public function test_atualizar_license_com_dados_validos(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $user->assignRole(SystemRole::Financeiro->value);
        $project = Project::factory()->create(['company_id' => $company->id]);

        $license = License::factory()->create([
            'project_id' => $project->id,
            'status' => 'active',
        ]);

        Sanctum::actingAs($user);

        $newExpiryDate = Carbon::now()->addDays(120)->format('Y-m-d');

        $response = $this->putJson("/api/v1/licenses/{$license->id}", [
            'expiry_date' => $newExpiryDate,
            'status' => 'pending_renewal',
            'notes' => 'Licença renovada',
        ], [
            'X-Company-Id' => $company->id,
        ])
            ->assertOk()
            ->assertJsonPath('data.status', 'pending_renewal')
            ->assertJsonPath('data.notes', 'Licença renovada');

        $this->assertDatabaseHas('licenses', [
            'id' => $license->id,
            'status' => 'pending_renewal',
            'updated_by' => $user->id,
        ]);

        // Verify expiry_date separately using whereDate
        $updatedLicense = DB::table('licenses')
            ->where('id', $license->id)
            ->whereDate('expiry_date', $newExpiryDate)
            ->where('status', 'pending_renewal')
            ->where('updated_by', $user->id)
            ->first();

        $this->assertNotNull($updatedLicense, 'License with correct expiry_date not found');
    }

    public function test_deletar_license_realiza_soft_delete(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $user->assignRole(SystemRole::Financeiro->value);
        $project = Project::factory()->create(['company_id' => $company->id]);

        $license = License::factory()->create([
            'project_id' => $project->id,
        ]);

        Sanctum::actingAs($user);

        $this->deleteJson("/api/v1/licenses/{$license->id}", [], [
            'X-Company-Id' => $company->id,
        ])
            ->assertStatus(204);

        $this->assertSoftDeleted('licenses', [
            'id' => $license->id,
        ]);
    }

    public function test_nao_pode_acessar_license_de_outra_empresa(): void
    {
        $user = User::factory()->create();
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();
        $user->companies()->attach($company1->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company1->id);
        $user->assignRole(SystemRole::Financeiro->value);
        $project = Project::factory()->create(['company_id' => $company2->id]);

        $license = License::factory()->create([
            'project_id' => $project->id,
        ]);

        Sanctum::actingAs($user);

        $this->getJson("/api/v1/licenses/{$license->id}", [
            'X-Company-Id' => $company1->id,
        ])
            ->assertStatus(403);
    }
}

