<?php

namespace Tests\Unit;

use App\Enums\PurchaseOrderStatus;
use App\Models\Company;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_order_can_be_created_with_valid_data(): void
    {
        $company = Company::factory()->create();
        $project = Project::factory()->create(['company_id' => $company->id]);
        $purchaseRequest = PurchaseRequest::factory()->create([
            'project_id' => $project->id,
        ]);

        $purchaseOrder = PurchaseOrder::factory()->create([
            'purchase_request_id' => $purchaseRequest->id,
            'status' => PurchaseOrderStatus::pending,
        ]);

        $this->assertDatabaseHas('purchase_orders', [
            'id' => $purchaseOrder->id,
            'purchase_request_id' => $purchaseRequest->id,
            'status' => 'pending',
        ]);
    }

    public function test_purchase_order_status_is_casted_to_enum(): void
    {
        $purchaseOrder = PurchaseOrder::factory()->create([
            'status' => PurchaseOrderStatus::pending,
        ]);

        $this->assertInstanceOf(PurchaseOrderStatus::class, $purchaseOrder->status);
        $this->assertEquals(PurchaseOrderStatus::pending, $purchaseOrder->status);
    }

    public function test_purchase_order_po_number_is_auto_generated(): void
    {
        $purchaseRequest = PurchaseRequest::factory()->create();
        $purchaseOrder = PurchaseOrder::factory()->create([
            'purchase_request_id' => $purchaseRequest->id,
        ]);

        $this->assertNotNull($purchaseOrder->po_number);
        $this->assertStringStartsWith('PO-', $purchaseOrder->po_number);
        $this->assertMatchesRegularExpression('/^PO-\d{6}-\d{4}$/', $purchaseOrder->po_number);
    }

    public function test_purchase_order_po_number_is_unique(): void
    {
        $purchaseRequest1 = PurchaseRequest::factory()->create();
        $purchaseRequest2 = PurchaseRequest::factory()->create();

        $po1 = PurchaseOrder::factory()->create([
            'purchase_request_id' => $purchaseRequest1->id,
        ]);

        $po2 = PurchaseOrder::factory()->create([
            'purchase_request_id' => $purchaseRequest2->id,
        ]);

        $this->assertNotEquals($po1->po_number, $po2->po_number);
    }

    public function test_purchase_order_po_number_sequence_increments(): void
    {
        $purchaseRequest1 = PurchaseRequest::factory()->create();
        $purchaseRequest2 = PurchaseRequest::factory()->create();
        $purchaseRequest3 = PurchaseRequest::factory()->create();

        $po1 = PurchaseOrder::factory()->create([
            'purchase_request_id' => $purchaseRequest1->id,
        ]);

        $po2 = PurchaseOrder::factory()->create([
            'purchase_request_id' => $purchaseRequest2->id,
        ]);

        $po3 = PurchaseOrder::factory()->create([
            'purchase_request_id' => $purchaseRequest3->id,
        ]);

        $sequence1 = (int) substr($po1->po_number, -4);
        $sequence2 = (int) substr($po2->po_number, -4);
        $sequence3 = (int) substr($po3->po_number, -4);

        $this->assertLessThan($sequence2, $sequence1);
        $this->assertLessThan($sequence3, $sequence2);
    }

    public function test_purchase_order_total_is_calculated_from_items(): void
    {
        $purchaseOrder = PurchaseOrder::factory()->create([
            'total' => 0,
        ]);

        PurchaseOrderItem::factory()->create([
            'purchase_order_id' => $purchaseOrder->id,
            'quantity' => 10,
            'unit_price' => 100.00,
            'total' => 1000.00,
        ]);

        PurchaseOrderItem::factory()->create([
            'purchase_order_id' => $purchaseOrder->id,
            'quantity' => 5,
            'unit_price' => 50.00,
            'total' => 250.00,
        ]);

        $purchaseOrder->calculateTotal();
        $purchaseOrder->save();

        $this->assertEquals(1250.00, $purchaseOrder->total);
    }

    public function test_purchase_order_has_purchase_request_relationship(): void
    {
        $purchaseRequest = PurchaseRequest::factory()->create();
        $purchaseOrder = PurchaseOrder::factory()->create([
            'purchase_request_id' => $purchaseRequest->id,
        ]);

        $this->assertEquals($purchaseRequest->id, $purchaseOrder->purchaseRequest->id);
    }

    public function test_purchase_order_has_items_relationship(): void
    {
        $purchaseOrder = PurchaseOrder::factory()->create();
        $item = PurchaseOrderItem::factory()->create([
            'purchase_order_id' => $purchaseOrder->id,
        ]);

        $this->assertTrue($purchaseOrder->items->contains($item));
    }

    public function test_purchase_order_uses_soft_deletes(): void
    {
        $purchaseOrder = PurchaseOrder::factory()->create();
        $purchaseOrder->delete();

        $this->assertSoftDeleted('purchase_orders', [
            'id' => $purchaseOrder->id,
        ]);
    }

    public function test_purchase_order_scope_for_project(): void
    {
        $company = Company::factory()->create();
        $project1 = Project::factory()->create(['company_id' => $company->id]);
        $project2 = Project::factory()->create(['company_id' => $company->id]);

        $pr1 = PurchaseRequest::factory()->create(['project_id' => $project1->id]);
        $pr2 = PurchaseRequest::factory()->create(['project_id' => $project2->id]);

        $po1 = PurchaseOrder::factory()->create(['purchase_request_id' => $pr1->id]);
        $po2 = PurchaseOrder::factory()->create(['purchase_request_id' => $pr2->id]);

        $result = PurchaseOrder::forProject($project1->id)->get();

        $this->assertCount(1, $result);
        $this->assertEquals($po1->id, $result->first()->id);
    }
}

