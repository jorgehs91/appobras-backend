<?php

namespace Tests\Unit;

use App\Models\CostItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseRequestItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PurchaseOrderItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_order_item_can_be_created_with_valid_data(): void
    {
        $purchaseOrder = PurchaseOrder::factory()->create();
        $costItem = CostItem::factory()->create();
        $purchaseRequestItem = PurchaseRequestItem::factory()->create();

        $item = PurchaseOrderItem::factory()->create([
            'purchase_order_id' => $purchaseOrder->id,
            'purchase_request_item_id' => $purchaseRequestItem->id,
            'cost_item_id' => $costItem->id,
            'description' => 'Item de teste',
            'quantity' => 10,
            'unit_price' => 100.00,
        ]);

        $this->assertDatabaseHas('purchase_order_items', [
            'id' => $item->id,
            'purchase_order_id' => $purchaseOrder->id,
            'purchase_request_item_id' => $purchaseRequestItem->id,
            'cost_item_id' => $costItem->id,
            'description' => 'Item de teste',
            'quantity' => 10,
            'unit_price' => 100.00,
            'total' => 1000.00,
        ]);
    }

    public function test_purchase_order_item_total_is_calculated_automatically(): void
    {
        $item = PurchaseOrderItem::factory()->create([
            'quantity' => 5,
            'unit_price' => 50.00,
        ]);

        $this->assertEquals(250.00, $item->total);
    }

    public function test_purchase_order_item_validation_fails_with_zero_quantity(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('A quantidade deve ser maior que zero.');

        PurchaseOrderItem::factory()->create([
            'quantity' => 0,
        ]);
    }

    public function test_purchase_order_item_validation_fails_with_negative_quantity(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('A quantidade deve ser maior que zero.');

        $purchaseOrder = PurchaseOrder::factory()->create();
        
        $item = new PurchaseOrderItem([
            'purchase_order_id' => $purchaseOrder->id,
            'description' => 'Test item',
            'quantity' => -1,
            'unit_price' => 10.00,
        ]);
        $item->save();
    }

    public function test_purchase_order_item_validation_fails_with_negative_unit_price(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('O preço unitário não pode ser negativo.');

        PurchaseOrderItem::factory()->create([
            'unit_price' => -10.00,
        ]);
    }

    public function test_purchase_order_item_has_purchase_order_relationship(): void
    {
        $purchaseOrder = PurchaseOrder::factory()->create();
        $item = PurchaseOrderItem::factory()->create([
            'purchase_order_id' => $purchaseOrder->id,
        ]);

        $this->assertEquals($purchaseOrder->id, $item->purchaseOrder->id);
    }

    public function test_purchase_order_item_has_purchase_request_item_relationship(): void
    {
        $purchaseRequestItem = PurchaseRequestItem::factory()->create();
        $item = PurchaseOrderItem::factory()->create([
            'purchase_request_item_id' => $purchaseRequestItem->id,
        ]);

        $this->assertEquals($purchaseRequestItem->id, $item->purchaseRequestItem->id);
    }

    public function test_purchase_order_item_has_cost_item_relationship(): void
    {
        $costItem = CostItem::factory()->create();
        $item = PurchaseOrderItem::factory()->create([
            'cost_item_id' => $costItem->id,
        ]);

        $this->assertEquals($costItem->id, $item->costItem->id);
    }

    public function test_purchase_order_total_is_recalculated_when_item_is_saved(): void
    {
        $purchaseOrder = PurchaseOrder::factory()->create([
            'total' => 0,
        ]);

        $item = PurchaseOrderItem::factory()->create([
            'purchase_order_id' => $purchaseOrder->id,
            'quantity' => 10,
            'unit_price' => 100.00,
        ]);

        $purchaseOrder->refresh();

        $this->assertEquals(1000.00, $purchaseOrder->total);
    }

    public function test_purchase_order_total_is_recalculated_when_item_is_deleted(): void
    {
        $purchaseOrder = PurchaseOrder::factory()->create([
            'total' => 0,
        ]);

        $item1 = PurchaseOrderItem::factory()->create([
            'purchase_order_id' => $purchaseOrder->id,
            'quantity' => 10,
            'unit_price' => 100.00,
        ]);

        $item2 = PurchaseOrderItem::factory()->create([
            'purchase_order_id' => $purchaseOrder->id,
            'quantity' => 5,
            'unit_price' => 50.00,
        ]);

        $purchaseOrder->refresh();
        $this->assertEquals(1250.00, $purchaseOrder->total);

        $item1->delete();
        $purchaseOrder->refresh();

        $this->assertEquals(250.00, $purchaseOrder->total);
    }

    public function test_purchase_order_item_uses_soft_deletes(): void
    {
        $item = PurchaseOrderItem::factory()->create();
        $item->delete();

        $this->assertSoftDeleted('purchase_order_items', [
            'id' => $item->id,
        ]);
    }
}

