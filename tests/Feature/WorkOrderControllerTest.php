<?php

namespace Tests\Feature;

use App\Enums\SystemRole;
use App\Enums\WorkOrderStatus;
use App\Models\Company;
use App\Models\Contract;
use App\Models\Contractor;
use App\Models\Project;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WorkOrderControllerTest extends TestCase
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

    public function test_listar_work_orders_requer_permissao(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        $contractor = Contractor::factory()->create(['company_id' => $company->id]);

        Sanctum::actingAs($user);

        $this->getJson("/api/v1/contractors/{$contractor->id}/work-orders", [
            'X-Company-Id' => $company->id,
        ])
            ->assertStatus(403);
    }

    public function test_listar_work_orders_com_role_financeiro(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $user->assignRole(SystemRole::Financeiro->value);
        $contractor = Contractor::factory()->create(['company_id' => $company->id]);
        $project = Project::factory()->create(['company_id' => $company->id]);
        $contract = Contract::factory()->create([
            'contractor_id' => $contractor->id,
            'project_id' => $project->id,
        ]);

        $workOrder1 = WorkOrder::factory()->create([
            'contract_id' => $contract->id,
            'status' => WorkOrderStatus::draft,
        ]);

        $workOrder2 = WorkOrder::factory()->create([
            'contract_id' => $contract->id,
            'status' => WorkOrderStatus::approved,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/contractors/{$contractor->id}/work-orders", [
            'X-Company-Id' => $company->id,
        ])
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $ids = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertContains($workOrder1->id, $ids);
        $this->assertContains($workOrder2->id, $ids);
    }

    public function test_filtrar_work_orders_por_status(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $user->assignRole(SystemRole::Financeiro->value);
        $contractor = Contractor::factory()->create(['company_id' => $company->id]);
        $project = Project::factory()->create(['company_id' => $company->id]);
        $contract = Contract::factory()->create([
            'contractor_id' => $contractor->id,
            'project_id' => $project->id,
        ]);

        WorkOrder::factory()->create([
            'contract_id' => $contract->id,
            'status' => WorkOrderStatus::draft,
        ]);

        WorkOrder::factory()->create([
            'contract_id' => $contract->id,
            'status' => WorkOrderStatus::approved,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/contractors/{$contractor->id}/work-orders?status=approved", [
            'X-Company-Id' => $company->id,
        ])
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->assertEquals(WorkOrderStatus::approved->value, $response->json('data.0.status'));
    }

    public function test_criar_work_order_com_dados_validos(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $user->assignRole(SystemRole::Financeiro->value);
        $contractor = Contractor::factory()->create(['company_id' => $company->id]);
        $project = Project::factory()->create(['company_id' => $company->id]);
        $contract = Contract::factory()->create([
            'contractor_id' => $contractor->id,
            'project_id' => $project->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/contractors/{$contractor->id}/work-orders", [
            'contract_id' => $contract->id,
            'description' => 'Execução de fundação',
            'value' => 25000.00,
            'due_date' => '2025-06-30',
            'status' => WorkOrderStatus::draft->value,
        ], [
            'X-Company-Id' => $company->id,
        ])
            ->assertStatus(201)
            ->assertJsonPath('data.description', 'Execução de fundação')
            ->assertJsonPath('data.value', '25000.00')
            ->assertJsonPath('data.status', WorkOrderStatus::draft->value);

        $this->assertDatabaseHas('work_orders', [
            'contract_id' => $contract->id,
            'description' => 'Execução de fundação',
            'value' => 25000.00,
            'status' => WorkOrderStatus::draft->value,
            'created_by' => $user->id,
        ]);
    }

    public function test_criar_work_order_requer_permissao(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        $contractor = Contractor::factory()->create(['company_id' => $company->id]);
        $project = Project::factory()->create(['company_id' => $company->id]);
        $contract = Contract::factory()->create([
            'contractor_id' => $contractor->id,
            'project_id' => $project->id,
        ]);

        Sanctum::actingAs($user);

        $this->postJson("/api/v1/contractors/{$contractor->id}/work-orders", [
            'contract_id' => $contract->id,
            'description' => 'Execução de fundação',
            'value' => 25000.00,
            'status' => WorkOrderStatus::draft->value,
        ], [
            'X-Company-Id' => $company->id,
        ])
            ->assertStatus(403);
    }

    public function test_visualizar_work_order_especifico(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $user->assignRole(SystemRole::Financeiro->value);
        $contractor = Contractor::factory()->create(['company_id' => $company->id]);
        $project = Project::factory()->create(['company_id' => $company->id]);
        $contract = Contract::factory()->create([
            'contractor_id' => $contractor->id,
            'project_id' => $project->id,
        ]);

        $workOrder = WorkOrder::factory()->create([
            'contract_id' => $contract->id,
            'value' => 25000.00,
        ]);

        Sanctum::actingAs($user);

        $this->getJson("/api/v1/contractors/{$contractor->id}/work-orders/{$workOrder->id}", [
            'X-Company-Id' => $company->id,
        ])
            ->assertOk()
            ->assertJsonPath('data.id', $workOrder->id)
            ->assertJsonPath('data.value', '25000.00');
    }

    public function test_atualizar_work_order_com_dados_validos(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $user->assignRole(SystemRole::Financeiro->value);
        $contractor = Contractor::factory()->create(['company_id' => $company->id]);
        $project = Project::factory()->create(['company_id' => $company->id]);
        $contract = Contract::factory()->create([
            'contractor_id' => $contractor->id,
            'project_id' => $project->id,
        ]);

        $workOrder = WorkOrder::factory()->create([
            'contract_id' => $contract->id,
            'value' => 25000.00,
            'status' => WorkOrderStatus::draft,
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson("/api/v1/contractors/{$contractor->id}/work-orders/{$workOrder->id}", [
            'value' => 30000.00,
            'description' => 'Descrição atualizada',
        ], [
            'X-Company-Id' => $company->id,
        ])
            ->assertOk()
            ->assertJsonPath('data.value', '30000.00')
            ->assertJsonPath('data.description', 'Descrição atualizada');

        $this->assertDatabaseHas('work_orders', [
            'id' => $workOrder->id,
            'value' => 30000.00,
            'updated_by' => $user->id,
        ]);
    }

    public function test_nao_pode_atualizar_work_order_approved(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $user->assignRole(SystemRole::Financeiro->value);
        $contractor = Contractor::factory()->create(['company_id' => $company->id]);
        $project = Project::factory()->create(['company_id' => $company->id]);
        $contract = Contract::factory()->create([
            'contractor_id' => $contractor->id,
            'project_id' => $project->id,
        ]);

        $workOrder = WorkOrder::factory()->create([
            'contract_id' => $contract->id,
            'status' => WorkOrderStatus::approved,
        ]);

        Sanctum::actingAs($user);

        $this->putJson("/api/v1/contractors/{$contractor->id}/work-orders/{$workOrder->id}", [
            'value' => 30000.00,
        ], [
            'X-Company-Id' => $company->id,
        ])
            ->assertStatus(403);
    }

    public function test_aprovar_work_order_draft(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $user->assignRole(SystemRole::Financeiro->value);
        $contractor = Contractor::factory()->create(['company_id' => $company->id]);
        $project = Project::factory()->create(['company_id' => $company->id]);
        $contract = Contract::factory()->create([
            'contractor_id' => $contractor->id,
            'project_id' => $project->id,
        ]);

        $workOrder = WorkOrder::factory()->create([
            'contract_id' => $contract->id,
            'status' => WorkOrderStatus::draft,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/contractors/{$contractor->id}/work-orders/{$workOrder->id}/approve", [], [
            'X-Company-Id' => $company->id,
        ])
            ->assertOk()
            ->assertJsonPath('data.status', WorkOrderStatus::approved->value)
            ->assertJsonPath('message', 'Ordem de serviço aprovada com sucesso.');

        $this->assertDatabaseHas('work_orders', [
            'id' => $workOrder->id,
            'status' => WorkOrderStatus::approved->value,
            'updated_by' => $user->id,
        ]);
    }

    public function test_nao_pode_aprovar_work_order_approved(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $user->assignRole(SystemRole::Financeiro->value);
        $contractor = Contractor::factory()->create(['company_id' => $company->id]);
        $project = Project::factory()->create(['company_id' => $company->id]);
        $contract = Contract::factory()->create([
            'contractor_id' => $contractor->id,
            'project_id' => $project->id,
        ]);

        $workOrder = WorkOrder::factory()->create([
            'contract_id' => $contract->id,
            'status' => WorkOrderStatus::approved,
        ]);

        Sanctum::actingAs($user);

        $this->postJson("/api/v1/contractors/{$contractor->id}/work-orders/{$workOrder->id}/approve", [], [
            'X-Company-Id' => $company->id,
        ])
            ->assertStatus(403);
    }

    public function test_deletar_work_order_realiza_soft_delete(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $user->assignRole(SystemRole::Financeiro->value);
        $contractor = Contractor::factory()->create(['company_id' => $company->id]);
        $project = Project::factory()->create(['company_id' => $company->id]);
        $contract = Contract::factory()->create([
            'contractor_id' => $contractor->id,
            'project_id' => $project->id,
        ]);

        $workOrder = WorkOrder::factory()->create([
            'contract_id' => $contract->id,
            'status' => WorkOrderStatus::draft,
        ]);

        Sanctum::actingAs($user);

        $this->deleteJson("/api/v1/contractors/{$contractor->id}/work-orders/{$workOrder->id}", [], [
            'X-Company-Id' => $company->id,
        ])
            ->assertStatus(204);

        $this->assertSoftDeleted('work_orders', [
            'id' => $workOrder->id,
        ]);
    }

    public function test_nao_pode_deletar_work_order_approved(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $user->assignRole(SystemRole::Financeiro->value);
        $contractor = Contractor::factory()->create(['company_id' => $company->id]);
        $project = Project::factory()->create(['company_id' => $company->id]);
        $contract = Contract::factory()->create([
            'contractor_id' => $contractor->id,
            'project_id' => $project->id,
        ]);

        $workOrder = WorkOrder::factory()->create([
            'contract_id' => $contract->id,
            'status' => WorkOrderStatus::approved,
        ]);

        Sanctum::actingAs($user);

        $this->deleteJson("/api/v1/contractors/{$contractor->id}/work-orders/{$workOrder->id}", [], [
            'X-Company-Id' => $company->id,
        ])
            ->assertStatus(403);
    }
}

