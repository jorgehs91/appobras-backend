<?php

namespace Tests\Feature;

use App\Enums\PaymentStatus;
use App\Enums\SystemRole;
use App\Models\Company;
use App\Models\Contract;
use App\Models\Contractor;
use App\Models\Payment;
use App\Models\Project;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PaymentControllerTest extends TestCase
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

    public function test_listar_payments_requer_permissao(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        $contractor = Contractor::factory()->create(['company_id' => $company->id]);

        Sanctum::actingAs($user);

        $this->getJson("/api/v1/contractors/{$contractor->id}/payments", [
            'X-Company-Id' => $company->id,
        ])
            ->assertStatus(403);
    }

    public function test_listar_payments_com_role_financeiro(): void
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

        $payment1 = Payment::factory()->create([
            'payable_type' => 'App\\Models\\Contract',
            'payable_id' => $contract->id,
            'amount' => 10000.00,
            'status' => PaymentStatus::pending,
        ]);

        $payment2 = Payment::factory()->create([
            'payable_type' => 'App\\Models\\Contract',
            'payable_id' => $contract->id,
            'amount' => 20000.00,
            'status' => PaymentStatus::paid,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/contractors/{$contractor->id}/payments", [
            'X-Company-Id' => $company->id,
        ])
            ->assertOk()
            ->assertJsonCount(2, 'data');

        $ids = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertContains($payment1->id, $ids);
        $this->assertContains($payment2->id, $ids);
    }

    public function test_filtrar_payments_por_status(): void
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

        Payment::factory()->create([
            'payable_type' => 'App\\Models\\Contract',
            'payable_id' => $contract->id,
            'status' => PaymentStatus::pending,
        ]);

        Payment::factory()->create([
            'payable_type' => 'App\\Models\\Contract',
            'payable_id' => $contract->id,
            'status' => PaymentStatus::paid,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/contractors/{$contractor->id}/payments?status=paid", [
            'X-Company-Id' => $company->id,
        ])
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->assertEquals(PaymentStatus::paid->value, $response->json('data.0.status'));
    }

    public function test_filtrar_payments_por_payable_type(): void
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
        ]);

        Payment::factory()->create([
            'payable_type' => 'App\\Models\\Contract',
            'payable_id' => $contract->id,
        ]);

        Payment::factory()->create([
            'payable_type' => 'App\\Models\\WorkOrder',
            'payable_id' => $workOrder->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/contractors/{$contractor->id}/payments?payable_type=App\\Models\\WorkOrder", [
            'X-Company-Id' => $company->id,
        ])
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->assertEquals('App\\Models\\WorkOrder', $response->json('data.0.payable_type'));
    }

    public function test_criar_payment_para_contract(): void
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

        $response = $this->postJson("/api/v1/contractors/{$contractor->id}/payments", [
            'payable_type' => 'App\\Models\\Contract',
            'payable_id' => $contract->id,
            'amount' => 10000.00,
            'due_date' => '2025-06-30',
            'status' => PaymentStatus::pending->value,
        ], [
            'X-Company-Id' => $company->id,
        ])
            ->assertStatus(201)
            ->assertJsonPath('data.amount', '10000.00')
            ->assertJsonPath('data.status', PaymentStatus::pending->value);

        $this->assertDatabaseHas('payments', [
            'payable_type' => 'App\\Models\\Contract',
            'payable_id' => $contract->id,
            'amount' => 10000.00,
            'status' => PaymentStatus::pending->value,
            'created_by' => $user->id,
        ]);
    }

    public function test_criar_payment_para_work_order(): void
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
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/contractors/{$contractor->id}/payments", [
            'payable_type' => 'App\\Models\\WorkOrder',
            'payable_id' => $workOrder->id,
            'amount' => 5000.00,
            'due_date' => '2025-07-15',
            'status' => PaymentStatus::pending->value,
        ], [
            'X-Company-Id' => $company->id,
        ])
            ->assertStatus(201)
            ->assertJsonPath('data.amount', '5000.00')
            ->assertJsonPath('data.payable_type', 'App\\Models\\WorkOrder');

        $this->assertDatabaseHas('payments', [
            'payable_type' => 'App\\Models\\WorkOrder',
            'payable_id' => $workOrder->id,
            'amount' => 5000.00,
        ]);
    }

    public function test_criar_payment_requer_permissao(): void
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

        $this->postJson("/api/v1/contractors/{$contractor->id}/payments", [
            'payable_type' => 'App\\Models\\Contract',
            'payable_id' => $contract->id,
            'amount' => 10000.00,
            'due_date' => '2025-06-30',
            'status' => PaymentStatus::pending->value,
        ], [
            'X-Company-Id' => $company->id,
        ])
            ->assertStatus(403);
    }

    public function test_nao_pode_criar_payment_para_contract_de_outro_contractor(): void
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

        $this->postJson("/api/v1/contractors/{$contractor1->id}/payments", [
            'payable_type' => 'App\\Models\\Contract',
            'payable_id' => $contract->id,
            'amount' => 10000.00,
            'due_date' => '2025-06-30',
            'status' => PaymentStatus::pending->value,
        ], [
            'X-Company-Id' => $company->id,
        ])
            ->assertStatus(404);
    }

    public function test_visualizar_payment_especifico(): void
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

        $payment = Payment::factory()->create([
            'payable_type' => 'App\\Models\\Contract',
            'payable_id' => $contract->id,
            'amount' => 10000.00,
        ]);

        Sanctum::actingAs($user);

        $this->getJson("/api/v1/contractors/{$contractor->id}/payments/{$payment->id}", [
            'X-Company-Id' => $company->id,
        ])
            ->assertOk()
            ->assertJsonPath('data.id', $payment->id)
            ->assertJsonPath('data.amount', '10000.00');
    }

    public function test_atualizar_payment_com_dados_validos(): void
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

        $payment = Payment::factory()->create([
            'payable_type' => 'App\\Models\\Contract',
            'payable_id' => $contract->id,
            'amount' => 10000.00,
            'status' => PaymentStatus::pending,
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson("/api/v1/contractors/{$contractor->id}/payments/{$payment->id}", [
            'amount' => 15000.00,
            'status' => PaymentStatus::paid->value,
            'paid_at' => now()->toIso8601String(),
        ], [
            'X-Company-Id' => $company->id,
        ])
            ->assertOk()
            ->assertJsonPath('data.amount', '15000.00')
            ->assertJsonPath('data.status', PaymentStatus::paid->value);

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'amount' => 15000.00,
            'status' => PaymentStatus::paid->value,
            'updated_by' => $user->id,
        ]);
    }

    public function test_deletar_payment_realiza_soft_delete(): void
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

        $payment = Payment::factory()->create([
            'payable_type' => 'App\\Models\\Contract',
            'payable_id' => $contract->id,
        ]);

        Sanctum::actingAs($user);

        $this->deleteJson("/api/v1/contractors/{$contractor->id}/payments/{$payment->id}", [], [
            'X-Company-Id' => $company->id,
        ])
            ->assertStatus(204);

        $this->assertSoftDeleted('payments', [
            'id' => $payment->id,
        ]);
    }
}

