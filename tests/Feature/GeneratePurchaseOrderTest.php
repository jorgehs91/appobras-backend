<?php

namespace Tests\Feature;

use App\Enums\PurchaseOrderStatus;
use App\Enums\PurchaseRequestStatus;
use App\Events\ApprovedPurchaseRequest;
use App\Jobs\GeneratePurchaseOrder;
use App\Models\Company;
use App\Models\CostItem;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class GeneratePurchaseOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_purchase_request_dispatches_event(): void
    {
        Event::fake([ApprovedPurchaseRequest::class]);

        $purchaseRequest = PurchaseRequest::factory()->create([
            'status' => PurchaseRequestStatus::submitted,
        ]);

        $purchaseRequest->status = PurchaseRequestStatus::approved;
        $purchaseRequest->save();

        Event::assertDispatched(ApprovedPurchaseRequest::class, function ($event) use ($purchaseRequest) {
            return $event->purchaseRequest->id === $purchaseRequest->id;
        });
    }

    public function test_approved_purchase_request_dispatches_job(): void
    {
        Queue::fake();

        $purchaseRequest = PurchaseRequest::factory()->create([
            'status' => PurchaseRequestStatus::submitted,
        ]);

        $purchaseRequest->status = PurchaseRequestStatus::approved;
        $purchaseRequest->save();

        Queue::assertPushed(GeneratePurchaseOrder::class, function ($job) use ($purchaseRequest) {
            return $job->purchaseRequest->id === $purchaseRequest->id;
        });
    }

    public function test_job_generates_purchase_order_from_approved_pr(): void
    {

        $company = Company::factory()->create();
        $project = Project::factory()->create(['company_id' => $company->id]);
        $supplier = Supplier::factory()->create();
        $costItem = CostItem::factory()->create();

        $purchaseRequest = PurchaseRequest::factory()->create([
            'project_id' => $project->id,
            'supplier_id' => $supplier->id,
            'status' => PurchaseRequestStatus::submitted,
        ]);

        $item1 = PurchaseRequestItem::factory()->create([
            'purchase_request_id' => $purchaseRequest->id,
            'cost_item_id' => $costItem->id,
            'quantity' => 10,
            'unit_price' => 100.00,
            'total' => 1000.00,
        ]);

        $item2 = PurchaseRequestItem::factory()->create([
            'purchase_request_id' => $purchaseRequest->id,
            'cost_item_id' => $costItem->id,
            'quantity' => 5,
            'unit_price' => 50.00,
            'total' => 250.00,
        ]);

        $purchaseRequest->calculateTotal();
        $purchaseRequest->save();

        // Update PR to approved status
        $purchaseRequest->status = PurchaseRequestStatus::approved;
        $purchaseRequest->save();

        // Execute job synchronously
        $job = new GeneratePurchaseOrder($purchaseRequest);
        $job->handle();

        // Verify PO was created
        $this->assertDatabaseHas('purchase_orders', [
            'purchase_request_id' => $purchaseRequest->id,
            'status' => PurchaseOrderStatus::pending->value,
            'total' => 1250.00,
        ]);

        $purchaseOrder = PurchaseOrder::where('purchase_request_id', $purchaseRequest->id)->first();
        $this->assertNotNull($purchaseOrder);
        $this->assertNotNull($purchaseOrder->po_number);
        $this->assertStringStartsWith('PO-', $purchaseOrder->po_number);

        // Verify PO items were created
        $this->assertDatabaseHas('purchase_order_items', [
            'purchase_order_id' => $purchaseOrder->id,
            'purchase_request_item_id' => $item1->id,
            'quantity' => 10,
            'unit_price' => 100.00,
            'total' => 1000.00,
        ]);

        $this->assertDatabaseHas('purchase_order_items', [
            'purchase_order_id' => $purchaseOrder->id,
            'purchase_request_item_id' => $item2->id,
            'quantity' => 5,
            'unit_price' => 50.00,
            'total' => 250.00,
        ]);

        $this->assertCount(2, $purchaseOrder->items);
    }

    public function test_job_is_idempotent_and_does_not_create_duplicate_po(): void
    {
        $purchaseRequest = PurchaseRequest::factory()->create([
            'status' => PurchaseRequestStatus::submitted,
        ]);

        PurchaseRequestItem::factory()->create([
            'purchase_request_id' => $purchaseRequest->id,
            'quantity' => 10,
            'unit_price' => 100.00,
        ]);

        // Create PO manually first (simulating first execution)
        $existingPo = PurchaseOrder::factory()->create([
            'purchase_request_id' => $purchaseRequest->id,
        ]);

        // Update PR to approved
        $purchaseRequest->status = PurchaseRequestStatus::approved;
        $purchaseRequest->save();

        // Try to generate PO again (simulating duplicate job execution)
        $job = new GeneratePurchaseOrder($purchaseRequest);
        $job->handle();

        // Verify only one PO exists
        $this->assertEquals(1, PurchaseOrder::where('purchase_request_id', $purchaseRequest->id)->count());
        $this->assertEquals($existingPo->id, PurchaseOrder::where('purchase_request_id', $purchaseRequest->id)->first()->id);
    }

    public function test_job_does_not_generate_po_for_non_approved_pr(): void
    {

        $purchaseRequest = PurchaseRequest::factory()->create([
            'status' => PurchaseRequestStatus::submitted,
        ]);

        $job = new GeneratePurchaseOrder($purchaseRequest);
        $job->handle();

        $this->assertDatabaseMissing('purchase_orders', [
            'purchase_request_id' => $purchaseRequest->id,
        ]);
    }

    public function test_purchase_order_has_correct_relationships(): void
    {
        $purchaseRequest = PurchaseRequest::factory()->create([
            'status' => PurchaseRequestStatus::submitted,
        ]);

        PurchaseRequestItem::factory()->create([
            'purchase_request_id' => $purchaseRequest->id,
        ]);

        // Update PR to approved
        $purchaseRequest->status = PurchaseRequestStatus::approved;
        $purchaseRequest->save();

        // Execute job
        $job = new GeneratePurchaseOrder($purchaseRequest);
        $job->handle();

        $purchaseOrder = PurchaseOrder::where('purchase_request_id', $purchaseRequest->id)->first();

        $this->assertNotNull($purchaseOrder);
        $this->assertEquals($purchaseRequest->id, $purchaseOrder->purchaseRequest->id);
        $this->assertCount(1, $purchaseOrder->items);

        $poItem = $purchaseOrder->items->first();
        $this->assertNotNull($poItem->purchaseRequestItem);
        $this->assertEquals($purchaseRequest->items->first()->id, $poItem->purchaseRequestItem->id);
    }
}

