<?php

namespace Tests\Feature;

use App\Enums\ExpenseStatus;
use App\Enums\SystemRole;
use App\Models\Budget;
use App\Models\Company;
use App\Models\CostItem;
use App\Models\Expense;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ExpenseControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create roles using enum values
        Role::create(['name' => SystemRole::Financeiro->value, 'guard_name' => 'sanctum']);
        Role::create(['name' => SystemRole::AdminObra->value, 'guard_name' => 'sanctum']);
        Role::create(['name' => SystemRole::Engenheiro->value, 'guard_name' => 'sanctum']);

        // Fake local storage (default for expense receipts)
        Storage::fake('local');
    }

    public function test_listar_expenses_requer_permissao(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $user->companies()->attach($company->id);
        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id, ['role' => 'Viewer']);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/projects/'.$project->id.'/expenses', [
            'X-Company-Id' => $company->id,
        ])
            ->assertStatus(403);
    }

    public function test_listar_expenses_com_role_financeiro(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $user->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $user->assignRole(SystemRole::Financeiro->value);
        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id, ['role' => 'Viewer']);

        $expense1 = Expense::factory()->create([
            'project_id' => $project->id,
            'amount' => 1500.00,
            'date' => '2025-12-29',
            'status' => ExpenseStatus::draft,
        ]);

        $expense2 = Expense::factory()->create([
            'project_id' => $project->id,
            'amount' => 2000.00,
            'date' => '2025-12-28',
            'status' => ExpenseStatus::approved,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/projects/'.$project->id.'/expenses', [
            'X-Company-Id' => $company->id,
        ])
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $ids = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertContains($expense1->id, $ids);
        $this->assertContains($expense2->id, $ids);
    }

    public function test_filtrar_expenses_por_status(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $user->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $user->assignRole(SystemRole::Financeiro->value);
        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id, ['role' => 'Viewer']);

        Expense::factory()->create([
            'project_id' => $project->id,
            'status' => ExpenseStatus::draft,
        ]);

        $approvedExpense = Expense::factory()->create([
            'project_id' => $project->id,
            'status' => ExpenseStatus::approved,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/projects/'.$project->id.'/expenses?status=approved', [
            'X-Company-Id' => $company->id,
        ])
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $approvedExpense->id);
    }

    public function test_criar_expense_com_receipt(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $user->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $user->assignRole(SystemRole::Financeiro->value);
        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id, ['role' => 'Viewer']);

        $file = UploadedFile::fake()->create('receipt.pdf', 100);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/projects/'.$project->id.'/expenses', [
            'amount' => 1500.00,
            'date' => '2025-12-29',
            'description' => 'Compra de materiais',
            'receipt' => $file,
            'status' => ExpenseStatus::approved->value,
        ], [
            'X-Company-Id' => $company->id,
        ])
            ->assertStatus(201)
            ->assertJson(fn ($json) => $json->where('data.amount', 1500)
                ->where('data.status', ExpenseStatus::approved->value)
                ->where('data.description', 'Compra de materiais')
            );

        $this->assertDatabaseHas('expenses', [
            'project_id' => $project->id,
            'amount' => 1500.00,
            'status' => ExpenseStatus::approved->value,
        ]);

        $expense = Expense::first();
        $this->assertNotNull($expense->receipt_path);
        Storage::disk('local')->assertExists($expense->receipt_path);
    }

    public function test_criar_expense_sem_receipt_em_draft(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $user->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $user->assignRole(SystemRole::Financeiro->value);
        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id, ['role' => 'Viewer']);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/projects/'.$project->id.'/expenses', [
            'amount' => 1500.00,
            'date' => '2025-12-29',
            'status' => ExpenseStatus::draft->value,
        ], [
            'X-Company-Id' => $company->id,
        ])
            ->assertStatus(201);

        $this->assertDatabaseHas('expenses', [
            'project_id' => $project->id,
            'status' => ExpenseStatus::draft->value,
        ]);
    }

    public function test_criar_expense_approved_sem_receipt_falha(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $user->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $user->assignRole(SystemRole::Financeiro->value);
        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id, ['role' => 'Viewer']);

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/projects/'.$project->id.'/expenses', [
            'amount' => 1500.00,
            'date' => '2025-12-29',
            'status' => ExpenseStatus::approved->value,
        ], [
            'X-Company-Id' => $company->id,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['receipt']);
    }

    public function test_criar_expense_com_cost_item(): void
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

        $response = $this->postJson('/api/v1/projects/'.$project->id.'/expenses', [
            'cost_item_id' => $costItem->id,
            'amount' => 1500.00,
            'date' => '2025-12-29',
            'status' => ExpenseStatus::draft->value,
        ], [
            'X-Company-Id' => $company->id,
        ])
            ->assertStatus(201)
            ->assertJsonPath('data.cost_item_id', $costItem->id);

        $this->assertDatabaseHas('expenses', [
            'cost_item_id' => $costItem->id,
            'project_id' => $project->id,
        ]);
    }

    public function test_visualizar_expense(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $user->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $user->assignRole(SystemRole::Financeiro->value);
        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id, ['role' => 'Viewer']);

        $expense = Expense::factory()->create([
            'project_id' => $project->id,
            'amount' => 1500.00,
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/expenses/'.$expense->id, [
            'X-Company-Id' => $company->id,
        ])
            ->assertOk()
            ->assertJsonPath('data.id', $expense->id)
            ->assertJson(fn ($json) => $json->where('data.amount', 1500));
    }

    public function test_atualizar_expense(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $user->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $user->assignRole(SystemRole::Financeiro->value);
        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id, ['role' => 'Viewer']);

        $expense = Expense::factory()->create([
            'project_id' => $project->id,
            'amount' => 1500.00,
            'status' => ExpenseStatus::draft,
            'receipt_path' => null, // No receipt
        ]);

        Sanctum::actingAs($user);

        // Should fail when changing to approved without receipt
        $response = $this->putJson('/api/v1/expenses/'.$expense->id, [
            'amount' => 2000.00,
            'description' => 'Descrição atualizada',
            'status' => ExpenseStatus::approved->value,
        ], [
            'X-Company-Id' => $company->id,
        ])
            ->assertStatus(422); // Should fail because approved status requires receipt

        $file = UploadedFile::fake()->create('receipt.pdf', 100);

        $response = $this->putJson('/api/v1/expenses/'.$expense->id, [
            'amount' => 2000.00,
            'description' => 'Descrição atualizada',
            'receipt' => $file,
            'status' => ExpenseStatus::approved->value,
        ], [
            'X-Company-Id' => $company->id,
        ])
            ->assertOk()
            ->assertJson(fn ($json) => $json->where('data.amount', 2000)
                ->where('data.description', 'Descrição atualizada')
                ->where('data.status', ExpenseStatus::approved->value)
            );

        $expense->refresh();
        $this->assertEquals(2000.00, $expense->amount);
        $this->assertEquals('Descrição atualizada', $expense->description);
        Storage::disk('local')->assertExists($expense->receipt_path);
    }

    public function test_remover_expense(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $user->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $user->assignRole(SystemRole::Financeiro->value);
        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id, ['role' => 'Viewer']);

        $expense = Expense::factory()->withReceipt()->create([
            'project_id' => $project->id,
            'receipt_path' => 'expenses/project-1/receipt.pdf',
        ]);

        // Create the file in fake storage
        Storage::disk('local')->put($expense->receipt_path, 'fake content');

        Sanctum::actingAs($user);

        $this->deleteJson('/api/v1/expenses/'.$expense->id, [], [
            'X-Company-Id' => $company->id,
        ])
            ->assertStatus(204);

        $this->assertSoftDeleted('expenses', ['id' => $expense->id]);
        Storage::disk('local')->assertMissing($expense->receipt_path);
    }

    public function test_download_receipt(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $user->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $user->assignRole(SystemRole::Financeiro->value);
        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id, ['role' => 'Viewer']);

        $expense = Expense::factory()->create([
            'project_id' => $project->id,
            'receipt_path' => 'expenses/project-1/receipt.pdf',
        ]);

        // Create the file in fake storage
        Storage::disk('local')->put($expense->receipt_path, 'fake pdf content');

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/expenses/'.$expense->id.'/receipt', [
            'X-Company-Id' => $company->id,
        ])
            ->assertOk();
    }

    public function test_download_receipt_sem_comprovante_retorna_404(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $user->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $user->assignRole(SystemRole::Financeiro->value);
        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id, ['role' => 'Viewer']);

        $expense = Expense::factory()->create([
            'project_id' => $project->id,
            'receipt_path' => null,
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/expenses/'.$expense->id.'/receipt', [
            'X-Company-Id' => $company->id,
        ])
            ->assertStatus(404);
    }

    public function test_filtrar_expenses_por_data(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $user->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $user->assignRole(SystemRole::Financeiro->value);
        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id, ['role' => 'Viewer']);

        Expense::factory()->create([
            'project_id' => $project->id,
            'date' => '2025-12-25',
        ]);

        $expenseInRange = Expense::factory()->create([
            'project_id' => $project->id,
            'date' => '2025-12-28',
        ]);

        Expense::factory()->create([
            'project_id' => $project->id,
            'date' => '2025-12-30',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/projects/'.$project->id.'/expenses?date_from=2025-12-27&date_to=2025-12-29', [
            'X-Company-Id' => $company->id,
        ])
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $expenseInRange->id);
    }

    public function test_pvxr_endpoint_requer_permissao(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $user->companies()->attach($company->id);
        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id, ['role' => 'Viewer']);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/projects/'.$project->id.'/pvxr', [
            'X-Company-Id' => $company->id,
        ])
            ->assertStatus(403);
    }

    public function test_pvxr_calcula_corretamente_por_cost_item(): void
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
            'planned_amount' => 50000.00,
            'name' => 'Material de Construção',
        ]);

        $costItem2 = CostItem::factory()->create([
            'budget_id' => $budget->id,
            'planned_amount' => 30000.00,
            'name' => 'Mão de Obra',
        ]);

        // Create approved expenses
        Expense::factory()->create([
            'project_id' => $project->id,
            'cost_item_id' => $costItem1->id,
            'amount' => 45000.00,
            'status' => ExpenseStatus::approved,
        ]);

        Expense::factory()->create([
            'project_id' => $project->id,
            'cost_item_id' => $costItem1->id,
            'amount' => 3000.00,
            'status' => ExpenseStatus::approved,
        ]);

        Expense::factory()->create([
            'project_id' => $project->id,
            'cost_item_id' => $costItem2->id,
            'amount' => 25000.00,
            'status' => ExpenseStatus::approved,
        ]);

        // Draft expense should not be counted
        Expense::factory()->create([
            'project_id' => $project->id,
            'cost_item_id' => $costItem1->id,
            'amount' => 10000.00,
            'status' => ExpenseStatus::draft,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/projects/'.$project->id.'/pvxr', [
            'X-Company-Id' => $company->id,
        ])
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'by_cost_item' => [
                        '*' => [
                            'cost_item_id',
                            'cost_item_name',
                            'planned',
                            'realized',
                            'variance',
                            'variance_percentage',
                        ],
                    ],
                    'by_category',
                    'total' => [
                        'planned',
                        'realized',
                        'variance',
                        'variance_percentage',
                    ],
                ],
            ]);

        $byCostItem = $response->json('data.by_cost_item');
        
        // Find cost_item1 in response
        $item1 = collect($byCostItem)->firstWhere('cost_item_id', $costItem1->id);
        $this->assertNotNull($item1);
        $this->assertEquals(50000.00, $item1['planned']);
        $this->assertEquals(48000.00, $item1['realized']); // 45000 + 3000
        $this->assertEquals(2000.00, $item1['variance']); // 50000 - 48000
        $this->assertEquals(4.00, $item1['variance_percentage']); // (2000/50000)*100

        // Find cost_item2 in response
        $item2 = collect($byCostItem)->firstWhere('cost_item_id', $costItem2->id);
        $this->assertNotNull($item2);
        $this->assertEquals(30000.00, $item2['planned']);
        $this->assertEquals(25000.00, $item2['realized']);
        $this->assertEquals(5000.00, $item2['variance']); // 30000 - 25000
        $this->assertEquals(16.67, round($item2['variance_percentage'], 2)); // (5000/30000)*100

        // Check total
        $total = $response->json('data.total');
        $this->assertEquals(80000.00, $total['planned']); // 50000 + 30000
        $this->assertEquals(73000.00, $total['realized']); // 48000 + 25000
        $this->assertEquals(7000.00, $total['variance']); // 80000 - 73000
    }

    public function test_pvxr_calcula_corretamente_por_category(): void
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
            'planned_amount' => 40000.00,
            'category' => 'Materiais',
        ]);

        $costItem2 = CostItem::factory()->create([
            'budget_id' => $budget->id,
            'planned_amount' => 30000.00,
            'category' => 'Materiais',
        ]);

        $costItem3 = CostItem::factory()->create([
            'budget_id' => $budget->id,
            'planned_amount' => 20000.00,
            'category' => 'Serviços',
        ]);

        Expense::factory()->create([
            'project_id' => $project->id,
            'cost_item_id' => $costItem1->id,
            'amount' => 35000.00,
            'status' => ExpenseStatus::approved,
        ]);

        Expense::factory()->create([
            'project_id' => $project->id,
            'cost_item_id' => $costItem2->id,
            'amount' => 25000.00,
            'status' => ExpenseStatus::approved,
        ]);

        Expense::factory()->create([
            'project_id' => $project->id,
            'cost_item_id' => $costItem3->id,
            'amount' => 18000.00,
            'status' => ExpenseStatus::approved,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/projects/'.$project->id.'/pvxr', [
            'X-Company-Id' => $company->id,
        ])
            ->assertOk();

        $byCategory = $response->json('data.by_category');
        
        // Find Materiais category
        $materiais = collect($byCategory)->firstWhere('category', 'Materiais');
        $this->assertNotNull($materiais);
        $this->assertEquals(70000.00, $materiais['planned']); // 40000 + 30000
        $this->assertEquals(60000.00, $materiais['realized']); // 35000 + 25000
        $this->assertEquals(10000.00, $materiais['variance']); // 70000 - 60000

        // Find Serviços category
        $servicos = collect($byCategory)->firstWhere('category', 'Serviços');
        $this->assertNotNull($servicos);
        $this->assertEquals(20000.00, $servicos['planned']);
        $this->assertEquals(18000.00, $servicos['realized']);
        $this->assertEquals(2000.00, $servicos['variance']); // 20000 - 18000
    }

    public function test_pvxr_retorna_vazio_quando_nao_ha_budget(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $user->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $user->assignRole(SystemRole::Financeiro->value);
        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id, ['role' => 'Viewer']);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/projects/'.$project->id.'/pvxr', [
            'X-Company-Id' => $company->id,
        ])
            ->assertOk()
            ->assertJson([
                'data' => [
                    'by_cost_item' => [],
                    'by_category' => [],
                    'total' => [
                        'planned' => 0,
                        'realized' => 0,
                        'variance' => 0,
                        'variance_percentage' => 0,
                    ],
                ],
            ]);
    }

    public function test_pvxr_usa_cache_redis(): void
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

        // First request - should calculate
        $response1 = $this->getJson('/api/v1/projects/'.$project->id.'/pvxr', [
            'X-Company-Id' => $company->id,
        ])
            ->assertOk();

        // Second request - should use cache
        $response2 = $this->getJson('/api/v1/projects/'.$project->id.'/pvxr', [
            'X-Company-Id' => $company->id,
        ])
            ->assertOk();

        // Both should return same data
        $this->assertEquals($response1->json(), $response2->json());
    }
}

