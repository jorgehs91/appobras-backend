<?php

namespace Tests\Unit;

use App\Models\Contract;
use App\Models\WorkOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_work_order_can_be_created_with_valid_data(): void
    {
        $contract = Contract::factory()->create();

        $workOrder = WorkOrder::factory()->create([
            'contract_id' => $contract->id,
            'description' => 'Test Work Order Description',
            'value' => 5000.00,
            'due_date' => '2026-06-30',
        ]);

        $this->assertDatabaseHas('work_orders', [
            'id' => $workOrder->id,
            'contract_id' => $contract->id,
            'description' => 'Test Work Order Description',
            'value' => 5000.00,
        ]);
    }

    public function test_work_order_has_contract_relationship(): void
    {
        $contract = Contract::factory()->create();
        $workOrder = WorkOrder::factory()->create([
            'contract_id' => $contract->id,
        ]);

        $this->assertEquals($contract->id, $workOrder->contract->id);
    }

    public function test_contract_has_work_orders_relationship(): void
    {
        $contract = Contract::factory()->create();
        WorkOrder::factory()->count(3)->create([
            'contract_id' => $contract->id,
        ]);

        $this->assertCount(3, $contract->workOrders);
    }

    public function test_work_order_uses_soft_deletes(): void
    {
        $workOrder = WorkOrder::factory()->create();
        $workOrder->delete();

        $this->assertSoftDeleted('work_orders', [
            'id' => $workOrder->id,
        ]);
    }

    public function test_work_order_value_is_casted_to_decimal(): void
    {
        $workOrder = WorkOrder::factory()->create([
            'value' => 1234.56,
        ]);

        $this->assertIsNumeric($workOrder->value);
        $this->assertEquals('1234.56', (string) $workOrder->value);
    }

    public function test_work_order_due_date_is_casted_to_date(): void
    {
        $workOrder = WorkOrder::factory()->create([
            'due_date' => '2026-06-30',
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $workOrder->due_date);
    }

    public function test_work_order_due_date_can_be_null(): void
    {
        $workOrder = WorkOrder::factory()->create([
            'due_date' => null,
        ]);

        $this->assertNull($workOrder->due_date);
    }

    public function test_work_order_has_audit_fields(): void
    {
        $user = User::factory()->create();
        $contract = Contract::factory()->create();
        
        // Simulate authentication for AuditTrait
        auth()->login($user);

        $workOrder = WorkOrder::create([
            'contract_id' => $contract->id,
            'description' => 'Test Work Order',
            'value' => 1000.00,
        ]);

        $this->assertEquals($user->id, $workOrder->created_by);
        
        // Update to trigger updated_by
        $workOrder->update(['value' => 2000.00]);
        $workOrder->refresh();
        
        $this->assertEquals($user->id, $workOrder->updated_by);
        
        auth()->logout();
    }
}

