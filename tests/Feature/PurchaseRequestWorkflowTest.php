<?php

namespace Tests\Feature;

use App\Enums\PurchaseOrderStatus;
use App\Enums\PurchaseRequestStatus;
use App\Enums\SystemRole;
use App\Models\Budget;
use App\Models\Company;
use App\Models\CostItem;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PurchaseRequestWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        // Create roles using enum values
        Role::create(['name' => SystemRole::Financeiro->value, 'guard_name' => 'sanctum']);
        Role::create(['name' => SystemRole::AdminObra->value, 'guard_name' => 'sanctum']);
    }

    public function test_complete_workflow_from_draft_to_approved_with_po_generation(): void
    {
        // Setup
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $user->assignRole(SystemRole::Financeiro->value);
        Sanctum::actingAs($user);

        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id);
        $budget = Budget::factory()->create(['project_id' => $project->id]);
        $costItem = CostItem::factory()->create(['budget_id' => $budget->id]);
        $supplier = Supplier::factory()->create();

        // Step 1: Create Supplier
        $supplierResponse = $this->withHeader('X-Company-Id', $company->id)
            ->postJson('/api/v1/suppliers', [
                'name' => 'Fornecedor Teste',
                'cnpj' => '12345678000190',
                'contact' => '(11) 98765-4321',
            ]);

        $supplierResponse->assertStatus(201);
        $supplierId = $supplierResponse->json('data.id');

        // Step 2: Create Purchase Request in draft
        $prResponse = $this->withHeader('X-Company-Id', $company->id)
            ->postJson("/api/v1/projects/{$project->id}/purchase-requests", [
                'supplier_id' => $supplierId,
                'items' => [
                    [
                        'cost_item_id' => $costItem->id,
                        'description' => 'Material de construção',
                        'quantity' => 10,
                        'unit_price' => 150.00,
                    ],
                    [
                        'cost_item_id' => $costItem->id,
                        'description' => 'Ferramentas',
                        'quantity' => 5,
                        'unit_price' => 80.00,
                    ],
                ],
            ]);

        $prResponse->assertStatus(201)
            ->assertJsonPath('data.status', 'draft')
            ->assertJsonCount(2, 'data.items');

        $prId = $prResponse->json('data.id');
        $this->assertDatabaseHas('purchase_requests', [
            'id' => $prId,
            'status' => PurchaseRequestStatus::draft->value,
            'total' => 1900.00, // (10 * 150) + (5 * 80)
        ]);

        // Step 3: Submit Purchase Request
        $submitResponse = $this->withHeader('X-Company-Id', $company->id)
            ->postJson("/api/v1/purchase-requests/{$prId}/submit");

        $submitResponse->assertStatus(200)
            ->assertJsonPath('data.status', 'submitted');

        $this->assertDatabaseHas('purchase_requests', [
            'id' => $prId,
            'status' => PurchaseRequestStatus::submitted->value,
        ]);

        // Step 4: Approve Purchase Request
        $approveResponse = $this->withHeader('X-Company-Id', $company->id)
            ->postJson("/api/v1/purchase-requests/{$prId}/approve");

        $approveResponse->assertStatus(200)
            ->assertJsonPath('data.status', 'approved')
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'status',
                    'total',
                ],
                'message',
            ]);

        $this->assertDatabaseHas('purchase_requests', [
            'id' => $prId,
            'status' => PurchaseRequestStatus::approved->value,
        ]);

        // Step 5: Verify Purchase Order was generated
        Queue::assertPushed(\App\Jobs\GeneratePurchaseOrder::class);

        // Execute job synchronously to verify PO creation
        Queue::fake();
        $purchaseRequest = PurchaseRequest::find($prId);
        $purchaseRequest->status = PurchaseRequestStatus::approved;
        $purchaseRequest->save();

        $job = new \App\Jobs\GeneratePurchaseOrder($purchaseRequest);
        $job->handle();

        $this->assertDatabaseHas('purchase_orders', [
            'purchase_request_id' => $prId,
            'status' => PurchaseOrderStatus::pending->value,
            'total' => 1900.00,
        ]);

        $purchaseOrder = PurchaseOrder::where('purchase_request_id', $prId)->first();
        $this->assertNotNull($purchaseOrder);
        $this->assertNotNull($purchaseOrder->po_number);
        $this->assertStringStartsWith('PO-', $purchaseOrder->po_number);
        $this->assertCount(2, $purchaseOrder->items);
    }

    public function test_cannot_submit_pr_without_items(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $user->assignRole(SystemRole::Financeiro->value);
        Sanctum::actingAs($user);

        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id);
        $supplier = Supplier::factory()->create();

        // Create PR without items (manually to bypass validation)
        $pr = PurchaseRequest::factory()->create([
            'project_id' => $project->id,
            'supplier_id' => $supplier->id,
            'status' => PurchaseRequestStatus::draft,
        ]);

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->postJson("/api/v1/purchase-requests/{$pr->id}/submit");

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Não é possível submeter uma requisição de compra sem itens.');
    }

    public function test_cannot_approve_draft_pr(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $user->assignRole(SystemRole::Financeiro->value);
        Sanctum::actingAs($user);

        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id);
        $supplier = Supplier::factory()->create();

        $pr = PurchaseRequest::factory()->create([
            'project_id' => $project->id,
            'supplier_id' => $supplier->id,
            'status' => PurchaseRequestStatus::draft,
        ]);

        PurchaseRequestItem::factory()->create([
            'purchase_request_id' => $pr->id,
        ]);

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->postJson("/api/v1/purchase-requests/{$pr->id}/approve");

        // Should fail because PR is not in submitted status
        $response->assertStatus(403);
    }

    public function test_cannot_reject_draft_pr(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $user->assignRole(SystemRole::Financeiro->value);
        Sanctum::actingAs($user);

        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id);
        $supplier = Supplier::factory()->create();

        $pr = PurchaseRequest::factory()->create([
            'project_id' => $project->id,
            'supplier_id' => $supplier->id,
            'status' => PurchaseRequestStatus::draft,
        ]);

        PurchaseRequestItem::factory()->create([
            'purchase_request_id' => $pr->id,
        ]);

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->postJson("/api/v1/purchase-requests/{$pr->id}/reject", [
                'reason' => 'Test rejection',
            ]);

        // Should fail because PR is not in submitted status
        $response->assertStatus(403);
    }

    public function test_reject_pr_with_reason(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $user->assignRole(SystemRole::Financeiro->value);
        Sanctum::actingAs($user);

        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id);
        $supplier = Supplier::factory()->create();

        $pr = PurchaseRequest::factory()->create([
            'project_id' => $project->id,
            'supplier_id' => $supplier->id,
            'status' => PurchaseRequestStatus::submitted,
        ]);

        PurchaseRequestItem::factory()->create([
            'purchase_request_id' => $pr->id,
        ]);

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->postJson("/api/v1/purchase-requests/{$pr->id}/reject", [
                'reason' => 'Orçamento insuficiente',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'rejected');

        $pr->refresh();
        $this->assertEquals(PurchaseRequestStatus::rejected, $pr->status);
        $this->assertStringContainsString('Orçamento insuficiente', $pr->notes);
    }

    public function test_cannot_edit_approved_pr(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $user->assignRole(SystemRole::Financeiro->value);
        Sanctum::actingAs($user);

        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id);
        $supplier = Supplier::factory()->create();

        $pr = PurchaseRequest::factory()->create([
            'project_id' => $project->id,
            'supplier_id' => $supplier->id,
            'status' => PurchaseRequestStatus::approved,
        ]);

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->putJson("/api/v1/purchase-requests/{$pr->id}", [
                'notes' => 'Tentativa de edição',
            ]);

        // Should fail because approved PRs cannot be edited
        $response->assertStatus(422);
    }

    public function test_can_edit_rejected_pr(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $user->assignRole(SystemRole::Financeiro->value);
        Sanctum::actingAs($user);

        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id);
        $supplier = Supplier::factory()->create();

        $pr = PurchaseRequest::factory()->create([
            'project_id' => $project->id,
            'supplier_id' => $supplier->id,
            'status' => PurchaseRequestStatus::rejected,
        ]);

        PurchaseRequestItem::factory()->create([
            'purchase_request_id' => $pr->id,
        ]);

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->putJson("/api/v1/purchase-requests/{$pr->id}", [
                'notes' => 'PR corrigida após rejeição',
                'items' => [
                    [
                        'description' => 'Item atualizado',
                        'quantity' => 20,
                        'unit_price' => 100.00,
                    ],
                ],
            ]);

        $response->assertStatus(200);
        $pr->refresh();
        $this->assertEquals('PR corrigida após rejeição', $pr->notes);
    }

    public function test_unauthorized_user_cannot_approve_pr(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        // User without Financeiro role
        Sanctum::actingAs($user);

        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id);
        $supplier = Supplier::factory()->create();

        $pr = PurchaseRequest::factory()->create([
            'project_id' => $project->id,
            'supplier_id' => $supplier->id,
            'status' => PurchaseRequestStatus::submitted,
        ]);

        PurchaseRequestItem::factory()->create([
            'purchase_request_id' => $pr->id,
        ]);

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->postJson("/api/v1/purchase-requests/{$pr->id}/approve");

        // Should fail because user doesn't have budget access
        $response->assertStatus(403);
    }

    public function test_workflow_with_multiple_status_transitions(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        app(\Spatie\Permission\PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        $user->assignRole(SystemRole::Financeiro->value);
        Sanctum::actingAs($user);

        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id);
        $supplier = Supplier::factory()->create();
        $budget = Budget::factory()->create(['project_id' => $project->id]);
        $costItem = CostItem::factory()->create(['budget_id' => $budget->id]);

        // Create PR
        $prResponse = $this->withHeader('X-Company-Id', $company->id)
            ->postJson("/api/v1/projects/{$project->id}/purchase-requests", [
                'supplier_id' => $supplier->id,
                'items' => [
                    [
                        'cost_item_id' => $costItem->id,
                        'description' => 'Test item',
                        'quantity' => 1,
                        'unit_price' => 100.00,
                    ],
                ],
            ]);

        $prId = $prResponse->json('data.id');

        // Submit
        $this->withHeader('X-Company-Id', $company->id)
            ->postJson("/api/v1/purchase-requests/{$prId}/submit")
            ->assertStatus(200);

        // Reject
        $this->withHeader('X-Company-Id', $company->id)
            ->postJson("/api/v1/purchase-requests/{$prId}/reject", ['reason' => 'Needs revision'])
            ->assertStatus(200);

        // Edit (rejected PR can be edited) - this also changes status back to draft
        $this->withHeader('X-Company-Id', $company->id)
            ->putJson("/api/v1/purchase-requests/{$prId}", [
                'notes' => 'Revised PR',
                'status' => PurchaseRequestStatus::draft->value,
            ])
            ->assertStatus(200);

        // Submit again (now in draft status)
        $this->withHeader('X-Company-Id', $company->id)
            ->postJson("/api/v1/purchase-requests/{$prId}/submit")
            ->assertStatus(200);

        // Approve
        $this->withHeader('X-Company-Id', $company->id)
            ->postJson("/api/v1/purchase-requests/{$prId}/approve")
            ->assertStatus(200);

        // Verify final state
        $pr = PurchaseRequest::find($prId);
        $this->assertEquals(PurchaseRequestStatus::approved, $pr->status);
    }
}

