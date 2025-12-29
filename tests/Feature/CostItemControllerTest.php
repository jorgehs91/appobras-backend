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

class CostItemControllerTest extends TestCase
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

    public function test_listar_cost_items_requer_permissao(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $user->companies()->attach($company->id);
        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id, ['role' => 'Viewer']);

        $budget = Budget::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'total_planned' => 100000.00,
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/projects/'.$project->id.'/budgets/'.$budget->id.'/cost-items', [
            'X-Company-Id' => $company->id,
        ])
            ->assertStatus(403);
    }

    public function test_listar_cost_items_com_role_financeiro(): void
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

        $costItem1 = CostItem::factory()->create([
            'budget_id' => $budget->id,
            'name' => 'Item 1',
            'category' => 'Materiais',
            'planned_amount' => 30000.00,
        ]);

        $costItem2 = CostItem::factory()->create([
            'budget_id' => $budget->id,
            'name' => 'Item 2',
            'category' => 'Mão de Obra',
            'planned_amount' => 40000.00,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/projects/'.$project->id.'/budgets/'.$budget->id.'/cost-items', [
            'X-Company-Id' => $company->id,
        ])
            ->assertOk()
            ->assertJsonCount(2, 'data');
        
        // Items are ordered by category then name, so we check both IDs are present
        $ids = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertContains($costItem1->id, $ids);
        $this->assertContains($costItem2->id, $ids);
    }

    public function test_filtrar_cost_items_por_categoria(): void
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
            'category' => 'Mão de Obra',
            'planned_amount' => 40000.00,
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/projects/'.$project->id.'/budgets/'.$budget->id.'/cost-items?category=Materiais', [
            'X-Company-Id' => $company->id,
        ])
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.category', 'Materiais');
    }

    public function test_criar_cost_item(): void
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

        $this->postJson('/api/v1/projects/'.$project->id.'/budgets/'.$budget->id.'/cost-items', [
            'name' => 'Cimento',
            'category' => 'Materiais',
            'planned_amount' => 50000.00,
            'unit' => 'kg',
        ], ['X-Company-Id' => $company->id])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Cimento')
            ->assertJsonPath('data.category', 'Materiais')
            ->assertJson(fn ($json) => 
                $json->where('data.planned_amount', fn ($value) => abs((float) $value - 50000.0) < 0.01)->etc()
            )
            ->assertJsonPath('data.unit', 'kg')
            ->assertJsonPath('data.budget_id', $budget->id);

        $this->assertDatabaseHas('cost_items', [
            'budget_id' => $budget->id,
            'name' => 'Cimento',
            'category' => 'Materiais',
            'planned_amount' => 50000.00,
            'created_by' => $user->id,
        ]);
    }

    public function test_criar_cost_item_respeita_limite_budget(): void
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
            'planned_amount' => 60000.00,
        ]);

        Sanctum::actingAs($user);

        // Tentar criar item que excederia o total (60000 + 50000 = 110000 > 100000)
        $this->postJson('/api/v1/projects/'.$project->id.'/budgets/'.$budget->id.'/cost-items', [
            'name' => 'Item que excede',
            'category' => 'Materiais',
            'planned_amount' => 50000.00,
        ], ['X-Company-Id' => $company->id])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['planned_amount']);
    }

    public function test_visualizar_cost_item(): void
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

        $costItem = CostItem::factory()->create([
            'budget_id' => $budget->id,
            'name' => 'Item Teste',
            'planned_amount' => 50000.00,
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/cost-items/'.$costItem->id, [
            'X-Company-Id' => $company->id,
        ])
            ->assertOk()
            ->assertJsonPath('data.id', $costItem->id)
            ->assertJsonPath('data.name', 'Item Teste')
            ->assertJson(fn ($json) => 
                $json->where('data.planned_amount', fn ($value) => abs((float) $value - 50000.0) < 0.01)->etc()
            );
    }

    public function test_atualizar_cost_item(): void
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

        $costItem = CostItem::factory()->create([
            'budget_id' => $budget->id,
            'name' => 'Item Original',
            'planned_amount' => 30000.00,
        ]);

        Sanctum::actingAs($user);

        $this->putJson('/api/v1/cost-items/'.$costItem->id, [
            'name' => 'Item Atualizado',
            'category' => 'Materiais',
            'planned_amount' => 40000.00,
            'unit' => 'm²',
        ], ['X-Company-Id' => $company->id])
            ->assertOk()
            ->assertJsonPath('data.name', 'Item Atualizado')
            ->assertJson(fn ($json) => 
                $json->where('data.planned_amount', fn ($value) => abs((float) $value - 40000.0) < 0.01)->etc()
            )
            ->assertJsonPath('data.unit', 'm²');

        $this->assertDatabaseHas('cost_items', [
            'id' => $costItem->id,
            'name' => 'Item Atualizado',
            'planned_amount' => 40000.00,
            'updated_by' => $user->id,
        ]);
    }

    public function test_atualizar_cost_item_respeita_limite_budget(): void
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

        $costItem1 = CostItem::factory()->create([
            'budget_id' => $budget->id,
            'planned_amount' => 30000.00,
        ]);

        $costItem2 = CostItem::factory()->create([
            'budget_id' => $budget->id,
            'planned_amount' => 40000.00,
        ]);

        Sanctum::actingAs($user);

        // Tentar atualizar para um valor que excederia (30000 + 40000 = 70000, então 70001 excederia)
        // Mas na verdade: 90000 + 40000 = 130000 > 100000
        $this->putJson('/api/v1/cost-items/'.$costItem1->id, [
            'name' => $costItem1->name,
            'category' => $costItem1->category,
            'planned_amount' => 90000.00,
        ], ['X-Company-Id' => $company->id])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['planned_amount']);
    }

    public function test_deletar_cost_item(): void
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

        $costItem = CostItem::factory()->create([
            'budget_id' => $budget->id,
            'planned_amount' => 50000.00,
        ]);

        Sanctum::actingAs($user);

        $this->deleteJson('/api/v1/cost-items/'.$costItem->id, [], [
            'X-Company-Id' => $company->id,
        ])
            ->assertNoContent();

        $this->assertSoftDeleted('cost_items', [
            'id' => $costItem->id,
        ]);
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

        $budget = Budget::factory()->create([
            'company_id' => $company2->id,
            'project_id' => $project->id,
            'total_planned' => 100000.00,
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/projects/'.$project->id.'/budgets/'.$budget->id.'/cost-items', [
            'X-Company-Id' => $company1->id,
        ])
            ->assertStatus(403);
    }

    public function test_valida_campos_obrigatorios(): void
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

        $this->postJson('/api/v1/projects/'.$project->id.'/budgets/'.$budget->id.'/cost-items', [
            // Campos obrigatórios ausentes
        ], ['X-Company-Id' => $company->id])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'category', 'planned_amount']);
    }
}
