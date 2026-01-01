<?php

namespace Tests\Feature;

use App\Enums\ContractStatus;
use App\Enums\SystemRole;
use App\Models\Company;
use App\Models\Contract;
use App\Models\Contractor;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ContractControllerTest extends TestCase
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

    public function test_listar_contracts_requer_permissao(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        $contractor = Contractor::factory()->create(['company_id' => $company->id]);

        Sanctum::actingAs($user);

        $this->getJson("/api/v1/contractors/{$contractor->id}/contracts", [
            'X-Company-Id' => $company->id,
        ])
            ->assertStatus(403);
    }

    public function test_listar_contracts_com_role_financeiro(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $user->assignRole(SystemRole::Financeiro->value);
        $contractor = Contractor::factory()->create(['company_id' => $company->id]);
        $project = Project::factory()->create(['company_id' => $company->id]);

        $contract1 = Contract::factory()->create([
            'contractor_id' => $contractor->id,
            'project_id' => $project->id,
            'value' => 50000.00,
            'status' => ContractStatus::draft,
        ]);

        $contract2 = Contract::factory()->create([
            'contractor_id' => $contractor->id,
            'project_id' => $project->id,
            'value' => 75000.00,
            'status' => ContractStatus::active,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/contractors/{$contractor->id}/contracts", [
            'X-Company-Id' => $company->id,
        ])
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $ids = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertContains($contract1->id, $ids);
        $this->assertContains($contract2->id, $ids);
    }

    public function test_filtrar_contracts_por_status(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $user->assignRole(SystemRole::Financeiro->value);
        $contractor = Contractor::factory()->create(['company_id' => $company->id]);
        $project = Project::factory()->create(['company_id' => $company->id]);

        Contract::factory()->create([
            'contractor_id' => $contractor->id,
            'project_id' => $project->id,
            'status' => ContractStatus::draft,
        ]);

        Contract::factory()->create([
            'contractor_id' => $contractor->id,
            'project_id' => $project->id,
            'status' => ContractStatus::active,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/contractors/{$contractor->id}/contracts?status=active", [
            'X-Company-Id' => $company->id,
        ])
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->assertEquals(ContractStatus::active->value, $response->json('data.0.status'));
    }

    public function test_filtrar_contracts_por_project_id(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $user->assignRole(SystemRole::Financeiro->value);
        $contractor = Contractor::factory()->create(['company_id' => $company->id]);
        $project1 = Project::factory()->create(['company_id' => $company->id]);
        $project2 = Project::factory()->create(['company_id' => $company->id]);

        Contract::factory()->create([
            'contractor_id' => $contractor->id,
            'project_id' => $project1->id,
        ]);

        Contract::factory()->create([
            'contractor_id' => $contractor->id,
            'project_id' => $project2->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/contractors/{$contractor->id}/contracts?project_id={$project1->id}", [
            'X-Company-Id' => $company->id,
        ])
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->assertEquals($project1->id, $response->json('data.0.project_id'));
    }

    public function test_criar_contract_com_dados_validos(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $user->assignRole(SystemRole::Financeiro->value);
        $contractor = Contractor::factory()->create(['company_id' => $company->id]);
        $project = Project::factory()->create(['company_id' => $company->id]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/contractors/{$contractor->id}/contracts", [
            'project_id' => $project->id,
            'value' => 50000.00,
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
            'status' => ContractStatus::draft->value,
        ], [
            'X-Company-Id' => $company->id,
        ])
            ->assertStatus(201)
            ->assertJsonPath('data.value', '50000.00')
            ->assertJsonPath('data.status', ContractStatus::draft->value);

        $this->assertDatabaseHas('contracts', [
            'contractor_id' => $contractor->id,
            'project_id' => $project->id,
            'value' => 50000.00,
            'status' => ContractStatus::draft->value,
            'created_by' => $user->id,
        ]);
    }

    public function test_criar_contract_requer_permissao(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        $contractor = Contractor::factory()->create(['company_id' => $company->id]);
        $project = Project::factory()->create(['company_id' => $company->id]);

        Sanctum::actingAs($user);

        $this->postJson("/api/v1/contractors/{$contractor->id}/contracts", [
            'project_id' => $project->id,
            'value' => 50000.00,
            'start_date' => '2025-01-01',
            'status' => ContractStatus::draft->value,
        ], [
            'X-Company-Id' => $company->id,
        ])
            ->assertStatus(403);
    }

    public function test_criar_contract_com_projeto_de_outra_empresa_falha(): void
    {
        $user = User::factory()->create();
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();
        $user->companies()->attach($company1->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company1->id);
        $user->assignRole(SystemRole::Financeiro->value);
        $contractor = Contractor::factory()->create(['company_id' => $company1->id]);
        $project = Project::factory()->create(['company_id' => $company2->id]);

        Sanctum::actingAs($user);

        $this->postJson("/api/v1/contractors/{$contractor->id}/contracts", [
            'project_id' => $project->id,
            'value' => 50000.00,
            'start_date' => '2025-01-01',
            'status' => ContractStatus::draft->value,
        ], [
            'X-Company-Id' => $company1->id,
        ])
            ->assertStatus(403);
    }

    public function test_visualizar_contract_especifico(): void
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
            'value' => 50000.00,
        ]);

        Sanctum::actingAs($user);

        $this->getJson("/api/v1/contractors/{$contractor->id}/contracts/{$contract->id}", [
            'X-Company-Id' => $company->id,
        ])
            ->assertOk()
            ->assertJsonPath('data.id', $contract->id)
            ->assertJsonPath('data.value', '50000.00');
    }

    public function test_atualizar_contract_com_dados_validos(): void
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
            'value' => 50000.00,
            'status' => ContractStatus::draft,
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson("/api/v1/contractors/{$contractor->id}/contracts/{$contract->id}", [
            'value' => 75000.00,
            'status' => ContractStatus::active->value,
        ], [
            'X-Company-Id' => $company->id,
        ])
            ->assertOk()
            ->assertJsonPath('data.value', '75000.00')
            ->assertJsonPath('data.status', ContractStatus::active->value);

        $this->assertDatabaseHas('contracts', [
            'id' => $contract->id,
            'value' => 75000.00,
            'status' => ContractStatus::active->value,
            'updated_by' => $user->id,
        ]);
    }

    public function test_deletar_contract_realiza_soft_delete(): void
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

        $this->deleteJson("/api/v1/contractors/{$contractor->id}/contracts/{$contract->id}", [], [
            'X-Company-Id' => $company->id,
        ])
            ->assertStatus(204);

        $this->assertSoftDeleted('contracts', [
            'id' => $contract->id,
        ]);
    }

    public function test_nao_pode_acessar_contract_de_contractor_de_outra_empresa(): void
    {
        $user = User::factory()->create();
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();
        $user->companies()->attach($company1->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company1->id);
        $user->assignRole(SystemRole::Financeiro->value);
        $contractor1 = Contractor::factory()->create(['company_id' => $company1->id]);
        $contractor2 = Contractor::factory()->create(['company_id' => $company2->id]);
        $project = Project::factory()->create(['company_id' => $company2->id]);

        $contract = Contract::factory()->create([
            'contractor_id' => $contractor2->id,
            'project_id' => $project->id,
        ]);

        Sanctum::actingAs($user);

        $this->getJson("/api/v1/contractors/{$contractor2->id}/contracts/{$contract->id}", [
            'X-Company-Id' => $company1->id,
        ])
            ->assertStatus(403);
    }

    public function test_nao_pode_acessar_contract_que_nao_pertence_ao_contractor(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $user->assignRole(SystemRole::Financeiro->value);
        $contractor1 = Contractor::factory()->create(['company_id' => $company->id]);
        $contractor2 = Contractor::factory()->create(['company_id' => $company->id]);
        $project = Project::factory()->create(['company_id' => $company->id]);

        $contract = Contract::factory()->create([
            'contractor_id' => $contractor2->id,
            'project_id' => $project->id,
        ]);

        Sanctum::actingAs($user);

        $this->getJson("/api/v1/contractors/{$contractor1->id}/contracts/{$contract->id}", [
            'X-Company-Id' => $company->id,
        ])
            ->assertStatus(404);
    }
}

