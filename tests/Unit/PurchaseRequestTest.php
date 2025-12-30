<?php

namespace Tests\Unit;

use App\Enums\PurchaseRequestStatus;
use App\Models\Company;
use App\Models\Project;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PurchaseRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_request_can_be_created_with_valid_data(): void
    {
        $company = Company::factory()->create();
        $project = Project::factory()->create(['company_id' => $company->id]);
        $supplier = Supplier::factory()->create();

        $purchaseRequest = PurchaseRequest::factory()->create([
            'project_id' => $project->id,
            'supplier_id' => $supplier->id,
            'status' => PurchaseRequestStatus::draft,
        ]);

        $this->assertDatabaseHas('purchase_requests', [
            'id' => $purchaseRequest->id,
            'project_id' => $project->id,
            'supplier_id' => $supplier->id,
            'status' => 'draft',
        ]);
    }

    public function test_purchase_request_status_is_casted_to_enum(): void
    {
        $purchaseRequest = PurchaseRequest::factory()->create([
            'status' => PurchaseRequestStatus::draft,
        ]);

        $this->assertInstanceOf(PurchaseRequestStatus::class, $purchaseRequest->status);
        $this->assertEquals(PurchaseRequestStatus::draft, $purchaseRequest->status);
    }

    public function test_purchase_request_total_is_calculated_from_items(): void
    {
        $purchaseRequest = PurchaseRequest::factory()->create([
            'total' => 0,
        ]);

        PurchaseRequestItem::factory()->create([
            'purchase_request_id' => $purchaseRequest->id,
            'cost_item_id' => null,
            'quantity' => 10,
            'unit_price' => 100.00,
            'total' => 1000.00,
        ]);

        PurchaseRequestItem::factory()->create([
            'purchase_request_id' => $purchaseRequest->id,
            'cost_item_id' => null,
            'quantity' => 5,
            'unit_price' => 50.00,
            'total' => 250.00,
        ]);

        $purchaseRequest->calculateTotal();
        $purchaseRequest->save();

        $this->assertEquals(1250.00, $purchaseRequest->total);
    }

    public function test_purchase_request_validation_fails_with_invalid_status(): void
    {
        $this->expectException(\ValueError::class);

        // Try to create with invalid status - will fail at cast level
        try {
            $pr = new PurchaseRequest();
            $pr->status = 'invalid_status';
            $pr->project_id = Project::factory()->create()->id;
            $pr->save();
        } catch (\ValueError $e) {
            // This is expected - enum cast will fail
            throw $e;
        }
    }

    public function test_purchase_request_has_project_relationship(): void
    {
        $company = Company::factory()->create();
        $project = Project::factory()->create(['company_id' => $company->id]);
        $purchaseRequest = PurchaseRequest::factory()->create([
            'project_id' => $project->id,
        ]);

        $this->assertEquals($project->id, $purchaseRequest->project->id);
    }

    public function test_purchase_request_has_supplier_relationship(): void
    {
        $supplier = Supplier::factory()->create();
        $purchaseRequest = PurchaseRequest::factory()->create([
            'supplier_id' => $supplier->id,
        ]);

        $this->assertEquals($supplier->id, $purchaseRequest->supplier->id);
    }

    public function test_purchase_request_has_items_relationship(): void
    {
        $purchaseRequest = PurchaseRequest::factory()->create();
        $item = PurchaseRequestItem::factory()->create([
            'purchase_request_id' => $purchaseRequest->id,
        ]);

        $this->assertTrue($purchaseRequest->items->contains($item));
    }

    public function test_purchase_request_can_be_edited_when_draft(): void
    {
        $purchaseRequest = PurchaseRequest::factory()->create([
            'status' => PurchaseRequestStatus::draft,
        ]);

        $this->assertTrue($purchaseRequest->canBeEdited());
    }

    public function test_purchase_request_can_be_edited_when_rejected(): void
    {
        $purchaseRequest = PurchaseRequest::factory()->create([
            'status' => PurchaseRequestStatus::rejected,
        ]);

        $this->assertTrue($purchaseRequest->canBeEdited());
    }

    public function test_purchase_request_cannot_be_edited_when_approved(): void
    {
        $purchaseRequest = PurchaseRequest::factory()->create([
            'status' => PurchaseRequestStatus::approved,
        ]);

        $this->assertFalse($purchaseRequest->canBeEdited());
    }

    public function test_purchase_request_can_be_deleted_when_draft(): void
    {
        $purchaseRequest = PurchaseRequest::factory()->create([
            'status' => PurchaseRequestStatus::draft,
        ]);

        $this->assertTrue($purchaseRequest->canBeDeleted());
    }

    public function test_purchase_request_cannot_be_deleted_when_not_draft(): void
    {
        $purchaseRequest = PurchaseRequest::factory()->create([
            'status' => PurchaseRequestStatus::submitted,
        ]);

        $this->assertFalse($purchaseRequest->canBeDeleted());
    }

    public function test_purchase_request_uses_soft_deletes(): void
    {
        $purchaseRequest = PurchaseRequest::factory()->create();
        $purchaseRequest->delete();

        $this->assertSoftDeleted('purchase_requests', [
            'id' => $purchaseRequest->id,
        ]);
    }

    public function test_purchase_request_allows_transition_from_draft_to_submitted(): void
    {
        $purchaseRequest = PurchaseRequest::factory()->create([
            'status' => PurchaseRequestStatus::draft,
        ]);

        $purchaseRequest->status = PurchaseRequestStatus::submitted;
        $purchaseRequest->save();

        $this->assertEquals(PurchaseRequestStatus::submitted, $purchaseRequest->status);
    }

    public function test_purchase_request_allows_transition_from_submitted_to_approved(): void
    {
        $purchaseRequest = PurchaseRequest::factory()->create([
            'status' => PurchaseRequestStatus::submitted,
        ]);

        $purchaseRequest->status = PurchaseRequestStatus::approved;
        $purchaseRequest->save();

        $this->assertEquals(PurchaseRequestStatus::approved, $purchaseRequest->status);
    }

    public function test_purchase_request_allows_transition_from_submitted_to_rejected(): void
    {
        $purchaseRequest = PurchaseRequest::factory()->create([
            'status' => PurchaseRequestStatus::submitted,
        ]);

        $purchaseRequest->status = PurchaseRequestStatus::rejected;
        $purchaseRequest->save();

        $this->assertEquals(PurchaseRequestStatus::rejected, $purchaseRequest->status);
    }

    public function test_purchase_request_allows_transition_from_rejected_to_draft(): void
    {
        $purchaseRequest = PurchaseRequest::factory()->create([
            'status' => PurchaseRequestStatus::rejected,
        ]);

        $purchaseRequest->status = PurchaseRequestStatus::draft;
        $purchaseRequest->save();

        $this->assertEquals(PurchaseRequestStatus::draft, $purchaseRequest->status);
    }

    public function test_purchase_request_prevents_transition_from_draft_to_approved(): void
    {
        $purchaseRequest = PurchaseRequest::factory()->create([
            'status' => PurchaseRequestStatus::draft,
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Transição de status inválida');

        $purchaseRequest->status = PurchaseRequestStatus::approved;
        $purchaseRequest->save();
    }

    public function test_purchase_request_prevents_transition_from_approved_to_any_status(): void
    {
        $purchaseRequest = PurchaseRequest::factory()->create([
            'status' => PurchaseRequestStatus::approved,
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Transição de status inválida');

        $purchaseRequest->status = PurchaseRequestStatus::draft;
        $purchaseRequest->save();
    }

    public function test_purchase_request_prevents_editing_when_approved(): void
    {
        $purchaseRequest = PurchaseRequest::factory()->create([
            'status' => PurchaseRequestStatus::approved,
            'notes' => 'Original notes',
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Não é possível editar uma requisição de compra aprovada.');

        $purchaseRequest->notes = 'Modified notes';
        $purchaseRequest->save();
    }
}
