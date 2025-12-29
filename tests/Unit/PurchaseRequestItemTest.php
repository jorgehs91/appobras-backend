<?php

namespace Tests\Unit;

use App\Models\CostItem;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PurchaseRequestItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_request_item_can_be_created_with_valid_data(): void
    {
        $purchaseRequest = PurchaseRequest::factory()->create();
        $costItem = CostItem::factory()->create();

        $item = PurchaseRequestItem::factory()->create([
            'purchase_request_id' => $purchaseRequest->id,
            'cost_item_id' => $costItem->id,
            'description' => 'Item de teste',
            'quantity' => 10,
            'unit_price' => 100.00,
        ]);

        $this->assertDatabaseHas('purchase_request_items', [
            'id' => $item->id,
            'purchase_request_id' => $purchaseRequest->id,
            'cost_item_id' => $costItem->id,
            'description' => 'Item de teste',
            'quantity' => 10,
            'unit_price' => 100.00,
            'total' => 1000.00,
        ]);
    }

    public function test_purchase_request_item_total_is_calculated_automatically(): void
    {
        $item = PurchaseRequestItem::factory()->create([
            'quantity' => 5,
            'unit_price' => 50.00,
        ]);

        $this->assertEquals(250.00, $item->total);
    }

    public function test_purchase_request_item_validation_fails_with_zero_quantity(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('A quantidade deve ser maior que zero.');

        PurchaseRequestItem::factory()->create([
            'quantity' => 0,
        ]);
    }

    public function test_purchase_request_item_validation_fails_with_negative_quantity(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('A quantidade deve ser maior que zero.');

        PurchaseRequestItem::factory()->create([
            'quantity' => -1,
        ]);
    }

    public function test_purchase_request_item_validation_fails_with_negative_unit_price(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('O preço unitário não pode ser negativo.');

        PurchaseRequestItem::factory()->create([
            'unit_price' => -10.00,
        ]);
    }

    public function test_purchase_request_item_has_purchase_request_relationship(): void
    {
        $purchaseRequest = PurchaseRequest::factory()->create();
        $item = PurchaseRequestItem::factory()->create([
            'purchase_request_id' => $purchaseRequest->id,
        ]);

        $this->assertEquals($purchaseRequest->id, $item->purchaseRequest->id);
    }

    public function test_purchase_request_item_has_cost_item_relationship(): void
    {
        $costItem = CostItem::factory()->create();
        $item = PurchaseRequestItem::factory()->create([
            'cost_item_id' => $costItem->id,
        ]);

        $this->assertEquals($costItem->id, $item->costItem->id);
    }

    public function test_purchase_request_total_is_recalculated_when_item_is_saved(): void
    {
        $purchaseRequest = PurchaseRequest::factory()->create([
            'total' => 0,
        ]);

        $item = PurchaseRequestItem::factory()->create([
            'purchase_request_id' => $purchaseRequest->id,
            'quantity' => 10,
            'unit_price' => 100.00,
        ]);

        $purchaseRequest->refresh();

        $this->assertEquals(1000.00, $purchaseRequest->total);
    }

    public function test_purchase_request_total_is_recalculated_when_item_is_deleted(): void
    {
        $purchaseRequest = PurchaseRequest::factory()->create([
            'total' => 0,
        ]);

        $item1 = PurchaseRequestItem::factory()->create([
            'purchase_request_id' => $purchaseRequest->id,
            'quantity' => 10,
            'unit_price' => 100.00,
        ]);

        $item2 = PurchaseRequestItem::factory()->create([
            'purchase_request_id' => $purchaseRequest->id,
            'quantity' => 5,
            'unit_price' => 50.00,
        ]);

        $purchaseRequest->refresh();
        $this->assertEquals(1250.00, $purchaseRequest->total);

        $item1->delete();
        $purchaseRequest->refresh();

        $this->assertEquals(250.00, $purchaseRequest->total);
    }

    public function test_purchase_request_item_uses_soft_deletes(): void
    {
        $item = PurchaseRequestItem::factory()->create();
        $item->delete();

        $this->assertSoftDeleted('purchase_request_items', [
            'id' => $item->id,
        ]);
    }
}
