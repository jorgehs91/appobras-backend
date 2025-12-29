<?php

namespace Tests\Feature;

use App\Enums\SystemRole;
use App\Models\Budget;
use App\Models\Company;
use App\Models\CostItem;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BudgetControllerTest extends TestCase
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

    public function test_listar_budgets_requer_permissao(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $user->companies()->attach($company->id);
        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id, ['role' => 'Viewer']);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/projects/'.$project->id.'/budgets', [
            'X-Company-Id' => $company->id,
        ])
            ->assertStatus(403);
    }

    public function test_listar_budgets_com_role_financeiro(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $user->companies()->attach($company->id);
        
        // Set company context for role assignment
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $user->assignRole(SystemRole::Financeiro->value);
        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id, ['role' => 'Viewer']);

        $budget = Budget::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'total_planned' => 100000.00,
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/projects/'.$project->id.'/budgets', [
            'X-Company-Id' => $company->id,
        ])
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $budget->id)
            ->assertJson(fn ($json) => 
                $json->where('data.0.total_planned', fn ($value) => abs((float) $value - 100000.0) < 0.01)->etc()
            );
    }

    public function test_listar_budgets_com_role_admin_obra(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $user->companies()->attach($company->id);
        
        // Set company context for role assignment
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $user->assignRole(SystemRole::AdminObra->value);
        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id, ['role' => 'Viewer']);

        $budget = Budget::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'total_planned' => 150000.00,
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/projects/'.$project->id.'/budgets', [
            'X-Company-Id' => $company->id,
        ])
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $budget->id);
    }

    public function test_criar_budget(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $user->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $user->assignRole(SystemRole::Financeiro->value);
        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id, ['role' => 'Viewer']);

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/projects/'.$project->id.'/budgets', [
            'total_planned' => 200000.00,
        ], ['X-Company-Id' => $company->id])
            ->assertCreated()
            ->assertJson(fn ($json) => 
                $json->where('data.total_planned', fn ($value) => abs((float) $value - 200000.0) < 0.01)->etc()
            )
            ->assertJsonPath('data.project_id', $project->id)
            ->assertJsonPath('data.company_id', $company->id);

        $this->assertDatabaseHas('budgets', [
            'project_id' => $project->id,
            'company_id' => $company->id,
            'total_planned' => 200000.00,
            'created_by' => $user->id,
        ]);
    }

    public function test_visualizar_budget(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $user->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $user->assignRole(SystemRole::Financeiro->value);
        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id, ['role' => 'Viewer']);

        $budget = Budget::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'total_planned' => 100000.00,
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/budgets/'.$budget->id, [
            'X-Company-Id' => $company->id,
        ])
            ->assertOk()
            ->assertJsonPath('data.id', $budget->id)
            ->assertJson(fn ($json) => 
                $json->where('data.total_planned', fn ($value) => abs((float) $value - 100000.0) < 0.01)->etc()
            );
    }

    public function test_atualizar_budget(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $user->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $user->assignRole(SystemRole::Financeiro->value);
        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id, ['role' => 'Viewer']);

        $budget = Budget::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'total_planned' => 100000.00,
        ]);

        Sanctum::actingAs($user);

        $this->putJson('/api/v1/budgets/'.$budget->id, [
            'total_planned' => 150000.00,
        ], ['X-Company-Id' => $company->id])
            ->assertOk()
            ->assertJson(fn ($json) => 
                $json->where('data.total_planned', fn ($value) => abs((float) $value - 150000.0) < 0.01)->etc()
            );

        $this->assertDatabaseHas('budgets', [
            'id' => $budget->id,
            'total_planned' => 150000.00,
            'updated_by' => $user->id,
        ]);
    }

    public function test_deletar_budget_sem_cost_items(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $user->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $user->assignRole(SystemRole::Financeiro->value);
        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id, ['role' => 'Viewer']);

        $budget = Budget::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'total_planned' => 100000.00,
        ]);

        Sanctum::actingAs($user);

        $this->deleteJson('/api/v1/budgets/'.$budget->id, [], [
            'X-Company-Id' => $company->id,
        ])
            ->assertNoContent();

        $this->assertSoftDeleted('budgets', [
            'id' => $budget->id,
        ]);
    }

    public function test_nao_deletar_budget_com_cost_items(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $user->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $user->assignRole(SystemRole::Financeiro->value);
        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id, ['role' => 'Viewer']);

        $budget = Budget::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'total_planned' => 100000.00,
        ]);

        CostItem::factory()->create([
            'budget_id' => $budget->id,
            'planned_amount' => 50000.00,
        ]);

        Sanctum::actingAs($user);

        $this->deleteJson('/api/v1/budgets/'.$budget->id, [], [
            'X-Company-Id' => $company->id,
        ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Não é possível deletar um orçamento que contém itens de custo.');
    }

    public function test_summary_endpoint_retorna_agregacao_por_categoria(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $user->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $user->assignRole(SystemRole::Financeiro->value);
        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id, ['role' => 'Viewer']);

        $budget = Budget::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'total_planned' => 100000.00,
        ]);

        CostItem::factory()->create([
            'budget_id' => $budget->id,
            'category' => 'Materiais',
            'planned_amount' => 30000.00,
        ]);

        CostItem::factory()->create([
            'budget_id' => $budget->id,
            'category' => 'Materiais',
            'planned_amount' => 20000.00,
        ]);

        CostItem::factory()->create([
            'budget_id' => $budget->id,
            'category' => 'Mão de Obra',
            'planned_amount' => 40000.00,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/projects/'.$project->id.'/budget/summary', [
            'X-Company-Id' => $company->id,
        ])
            ->assertOk();

        $data = $response->json('data');
        $this->assertArrayHasKey('categories', $data);
        $this->assertArrayHasKey('total', $data);

        $categories = collect($data['categories']);
        $materiais = $categories->firstWhere('category', 'Materiais');
        $maoDeObra = $categories->firstWhere('category', 'Mão de Obra');

        $this->assertEquals(50000.00, $materiais['total_planned']);
        $this->assertEquals(40000.00, $maoDeObra['total_planned']);
        $this->assertEquals(90000.00, $data['total']);
    }

    public function test_summary_endpoint_sem_budget_retorna_vazio(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $user->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $user->assignRole(SystemRole::Financeiro->value);
        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id, ['role' => 'Viewer']);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/projects/'.$project->id.'/budget/summary', [
            'X-Company-Id' => $company->id,
        ])
            ->assertOk()
            ->assertJsonPath('data.categories', [])
            ->assertJsonPath('data.total', 0);
    }

    public function test_bloqueia_acesso_de_outra_company(): void
    {
        $user = User::factory()->create();
        $company1 = Company::query()->create(['name' => 'Company 1']);
        $company2 = Company::query()->create(['name' => 'Company 2']);
        $user->companies()->attach($company1->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company1->id);
        $user->assignRole(SystemRole::Financeiro->value);
        $project = Project::factory()->create(['company_id' => $company2->id]);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/projects/'.$project->id.'/budgets', [
            'X-Company-Id' => $company1->id,
        ])
            ->assertStatus(403);
    }
}
