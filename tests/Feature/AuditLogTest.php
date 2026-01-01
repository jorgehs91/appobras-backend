<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Contractor;
use App\Models\Phase;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Criar permissão e role para testes
        $permission = Permission::create(['name' => 'users.update', 'guard_name' => 'sanctum']);
        $role = Role::create(['name' => 'Admin', 'guard_name' => 'sanctum']);
        $role->givePermissionTo($permission);
    }

    public function test_audit_log_observer_creates_log_on_model_creation(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $project = Project::factory()->create(['company_id' => $company->id]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'auditable_id' => $project->id,
            'auditable_type' => Project::class,
            'event' => 'created',
        ]);

        $auditLog = AuditLog::where('auditable_id', $project->id)
            ->where('auditable_type', Project::class)
            ->where('event', 'created')
            ->first();

        $this->assertNotNull($auditLog);
        $this->assertNotNull($auditLog->new_values);
        $this->assertNull($auditLog->old_values);
        $this->assertNotNull($auditLog->ip);
        $this->assertNotNull($auditLog->user_agent);
    }

    public function test_audit_log_observer_creates_log_on_model_update(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $project = Project::factory()->create(['company_id' => $company->id, 'name' => 'Original Name']);

        // Limpar logs de criação
        AuditLog::where('auditable_id', $project->id)->delete();

        $project->update(['name' => 'Updated Name']);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'auditable_id' => $project->id,
            'auditable_type' => Project::class,
            'event' => 'updated',
        ]);

        $auditLog = AuditLog::where('auditable_id', $project->id)
            ->where('event', 'updated')
            ->first();

        $this->assertNotNull($auditLog);
        $this->assertNotNull($auditLog->old_values);
        $this->assertNotNull($auditLog->new_values);
        $this->assertArrayHasKey('name', $auditLog->new_values);
        $this->assertEquals('Updated Name', $auditLog->new_values['name']);
    }

    public function test_audit_log_observer_creates_log_on_model_deletion(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $phase = Phase::factory()->create(['company_id' => $company->id]);

        // Limpar logs de criação
        AuditLog::where('auditable_id', $phase->id)->delete();

        $phase->delete();

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'auditable_id' => $phase->id,
            'auditable_type' => Phase::class,
            'event' => 'deleted',
        ]);

        $auditLog = AuditLog::where('auditable_id', $phase->id)
            ->where('event', 'deleted')
            ->first();

        $this->assertNotNull($auditLog);
        $this->assertNotNull($auditLog->old_values);
        $this->assertNull($auditLog->new_values);
    }

    public function test_audit_log_endpoint_requires_authentication(): void
    {
        $this->getJson('/api/v1/admin/audit-logs')
            ->assertUnauthorized();
    }

    public function test_audit_log_endpoint_requires_permission(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/admin/audit-logs', ['X-Company-Id' => $company->id])
            ->assertForbidden();
    }

    public function test_audit_log_endpoint_returns_logs_with_filters(): void
    {
        $company = Company::query()->create(['name' => 'Test Company']);
        $user = User::factory()->create();
        $user->companies()->attach($company->id);
        
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $adminRole = Role::where('name', 'Admin')->first();
        $user->assignRole($adminRole);
        
        Sanctum::actingAs($user);

        $project = Project::factory()->create(['company_id' => $company->id]);
        $task = Task::factory()->create(['company_id' => $company->id, 'project_id' => $project->id]);

        // Criar alguns logs manualmente para teste
        AuditLog::factory()->create([
            'user_id' => $user->id,
            'auditable_id' => $project->id,
            'auditable_type' => Project::class,
            'event' => 'created',
        ]);

        AuditLog::factory()->create([
            'user_id' => $user->id,
            'auditable_id' => $task->id,
            'auditable_type' => Task::class,
            'event' => 'updated',
        ]);

        // Teste sem filtros
        $response = $this->getJson('/api/v1/admin/audit-logs', ['X-Company-Id' => $company->id])
            ->assertOk();

        $this->assertGreaterThanOrEqual(2, count($response->json('data')));

        // Teste com filtro por projeto
        $response = $this->getJson('/api/v1/admin/audit-logs?project_id='.$project->id, ['X-Company-Id' => $company->id])
            ->assertOk();

        $data = $response->json('data');
        $this->assertGreaterThanOrEqual(1, count($data));

        // Teste com filtro por usuário
        $response = $this->getJson('/api/v1/admin/audit-logs?user_id='.$user->id, ['X-Company-Id' => $company->id])
            ->assertOk();

        $this->assertGreaterThanOrEqual(2, count($response->json('data')));

        // Teste com filtro por tipo de entidade
        $response = $this->getJson('/api/v1/admin/audit-logs?entity_type='.urlencode(Project::class), ['X-Company-Id' => $company->id])
            ->assertOk();

        $data = $response->json('data');
        foreach ($data as $log) {
            $this->assertEquals(Project::class, $log['auditable_type']);
        }

        // Teste com filtro por ação
        $response = $this->getJson('/api/v1/admin/audit-logs?action=created', ['X-Company-Id' => $company->id])
            ->assertOk();

        $data = $response->json('data');
        foreach ($data as $log) {
            $this->assertEquals('created', $log['event']);
        }
    }

    public function test_audit_log_endpoint_supports_pagination(): void
    {
        $company = Company::query()->create(['name' => 'Test Company']);
        $user = User::factory()->create();
        $user->companies()->attach($company->id);
        
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $adminRole = Role::where('name', 'Admin')->first();
        $user->assignRole($adminRole);
        
        Sanctum::actingAs($user);

        // Criar múltiplos logs
        AuditLog::factory()->count(25)->create([
            'user_id' => $user->id,
            'auditable_id' => Project::factory()->create(['company_id' => $company->id])->id,
            'auditable_type' => Project::class,
        ]);

        // Teste paginação padrão
        $response = $this->getJson('/api/v1/admin/audit-logs', ['X-Company-Id' => $company->id])
            ->assertOk();

        $this->assertArrayHasKey('current_page', $response->json());
        $this->assertArrayHasKey('per_page', $response->json());
        $this->assertArrayHasKey('total', $response->json());
        $this->assertCount(15, $response->json('data')); // Padrão é 15

        // Teste paginação customizada
        $response = $this->getJson('/api/v1/admin/audit-logs?per_page=10', ['X-Company-Id' => $company->id])
            ->assertOk();

        $this->assertCount(10, $response->json('data'));
    }

    public function test_audit_log_scope_by_project(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $user->companies()->attach($company->id);

        $project1 = Project::factory()->create(['company_id' => $company->id, 'name' => 'Project 1']);
        $project2 = Project::factory()->create(['company_id' => $company->id, 'name' => 'Project 2']);
        $phase = Phase::factory()->create(['company_id' => $company->id, 'project_id' => $project1->id]);
        $task = Task::factory()->create(['company_id' => $company->id, 'project_id' => $project1->id, 'phase_id' => $phase->id]);

        Sanctum::actingAs($user);

        // Criar logs para diferentes entidades do projeto 1
        $project1->update(['name' => 'Updated Project 1']);
        $phase->update(['name' => 'Updated Phase']);
        $task->update(['title' => 'Updated Task']);

        // Criar log para projeto 2
        $project2->update(['name' => 'Updated Project 2']);

        $logs = AuditLog::byProject($project1->id)->get();

        $this->assertGreaterThanOrEqual(3, $logs->count());
        foreach ($logs as $log) {
            $this->assertContains($log->auditable_type, [Project::class, Phase::class, Task::class]);
        }
    }

    public function test_audit_log_scope_by_company(): void
    {
        $user = User::factory()->create();
        $company1 = Company::query()->create(['name' => 'Company 1']);
        $company2 = Company::query()->create(['name' => 'Company 2']);
        $user->companies()->attach([$company1->id, $company2->id]);

        $project1 = Project::factory()->create(['company_id' => $company1->id]);
        $project2 = Project::factory()->create(['company_id' => $company2->id]);
        $contractor1 = Contractor::factory()->create(['company_id' => $company1->id]);
        $contractor2 = Contractor::factory()->create(['company_id' => $company2->id]);

        Sanctum::actingAs($user);

        $project1->update(['name' => 'Updated']);
        $contractor1->update(['name' => 'Updated']);
        $project2->update(['name' => 'Updated']);
        $contractor2->update(['name' => 'Updated']);

        $logs = AuditLog::byCompany($company1->id)->get();

        $this->assertGreaterThanOrEqual(2, $logs->count());
        foreach ($logs as $log) {
            $this->assertContains($log->auditable_type, [Project::class, Contractor::class]);
        }
    }

    public function test_audit_log_factory_creates_valid_log(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();

        $auditLog = AuditLog::factory()->create([
            'user_id' => $user->id,
            'auditable_id' => $project->id,
            'auditable_type' => Project::class,
        ]);

        $this->assertNotNull($auditLog);
        $this->assertEquals($user->id, $auditLog->user_id);
        $this->assertEquals($project->id, $auditLog->auditable_id);
        $this->assertEquals(Project::class, $auditLog->auditable_type);
        $this->assertNotNull($auditLog->event);
        $this->assertNotNull($auditLog->ip);
        $this->assertNotNull($auditLog->user_agent);
    }

    public function test_audit_log_polymorphic_relationship(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();

        $auditLog = AuditLog::factory()->create([
            'user_id' => $user->id,
            'auditable_id' => $project->id,
            'auditable_type' => Project::class,
        ]);

        $this->assertInstanceOf(Project::class, $auditLog->auditable);
        $this->assertEquals($project->id, $auditLog->auditable->id);
    }

    public function test_audit_log_user_relationship(): void
    {
        $user = User::factory()->create();
        $project = Project::factory()->create();

        $auditLog = AuditLog::factory()->create([
            'user_id' => $user->id,
            'auditable_id' => $project->id,
            'auditable_type' => Project::class,
        ]);

        $this->assertInstanceOf(User::class, $auditLog->user);
        $this->assertEquals($user->id, $auditLog->user->id);
    }
}

