<?php

namespace Tests\Unit;

use App\Enums\PaymentStatus;
use App\Models\Contract;
use App\Models\Payment;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_can_be_created_with_valid_data(): void
    {
        $workOrder = WorkOrder::factory()->create();

        $payment = Payment::factory()->forWorkOrder($workOrder)->create([
            'amount' => 5000.00,
            'due_date' => '2026-06-30',
            'status' => PaymentStatus::pending->value,
        ]);

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'payable_type' => WorkOrder::class,
            'payable_id' => $workOrder->id,
            'amount' => 5000.00,
            'status' => PaymentStatus::pending->value,
        ]);
    }

    public function test_payment_can_be_created_for_contract(): void
    {
        $contract = Contract::factory()->create();

        $payment = Payment::factory()->forContract($contract)->create([
            'amount' => 10000.00,
            'due_date' => '2026-07-15',
        ]);

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'payable_type' => Contract::class,
            'payable_id' => $contract->id,
        ]);
    }

    public function test_payment_has_polymorphic_relationship_to_work_order(): void
    {
        $workOrder = WorkOrder::factory()->create();
        $payment = Payment::factory()->forWorkOrder($workOrder)->create();

        $this->assertInstanceOf(WorkOrder::class, $payment->payable);
        $this->assertEquals($workOrder->id, $payment->payable->id);
    }

    public function test_payment_has_polymorphic_relationship_to_contract(): void
    {
        $contract = Contract::factory()->create();
        $payment = Payment::factory()->forContract($contract)->create();

        $this->assertInstanceOf(Contract::class, $payment->payable);
        $this->assertEquals($contract->id, $payment->payable->id);
    }

    public function test_work_order_has_payments_relationship(): void
    {
        $workOrder = WorkOrder::factory()->create();
        Payment::factory()->count(3)->forWorkOrder($workOrder)->create();

        $this->assertCount(3, $workOrder->payments);
    }

    public function test_contract_has_payments_relationship(): void
    {
        $contract = Contract::factory()->create();
        Payment::factory()->count(2)->forContract($contract)->create();

        $this->assertCount(2, $contract->payments);
    }

    public function test_payment_uses_soft_deletes(): void
    {
        $payment = Payment::factory()->create();
        $payment->delete();

        $this->assertSoftDeleted('payments', [
            'id' => $payment->id,
        ]);
    }

    public function test_payment_amount_is_casted_to_decimal(): void
    {
        $payment = Payment::factory()->create([
            'amount' => 1234.56,
        ]);

        $this->assertIsNumeric($payment->amount);
        $this->assertEquals('1234.56', (string) $payment->amount);
    }

    public function test_payment_due_date_is_casted_to_date(): void
    {
        $payment = Payment::factory()->create([
            'due_date' => '2026-06-30',
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $payment->due_date);
    }

    public function test_payment_status_is_casted_to_enum(): void
    {
        $payment = Payment::factory()->create([
            'status' => PaymentStatus::pending->value,
        ]);

        $this->assertInstanceOf(PaymentStatus::class, $payment->status);
        $this->assertEquals(PaymentStatus::pending, $payment->status);
    }

    public function test_payment_has_audit_fields(): void
    {
        $user = User::factory()->create();
        
        // Simulate authentication for AuditTrait
        auth()->login($user);

        $payment = Payment::factory()->create();

        $this->assertEquals($user->id, $payment->created_by);
        
        // Update to trigger updated_by
        $payment->update(['amount' => 2000.00]);
        $payment->refresh();
        
        $this->assertEquals($user->id, $payment->updated_by);
        
        auth()->logout();
    }

    public function test_payment_can_transition_from_pending_to_paid(): void
    {
        $payment = Payment::factory()->pending()->create();

        $payment->update([
            'status' => PaymentStatus::paid->value,
            'paid_at' => now(),
        ]);

        $this->assertEquals(PaymentStatus::paid, $payment->status);
        $this->assertNotNull($payment->paid_at);
    }

    public function test_payment_can_transition_from_pending_to_canceled(): void
    {
        $payment = Payment::factory()->pending()->create();

        $payment->update([
            'status' => PaymentStatus::canceled->value,
        ]);

        $this->assertEquals(PaymentStatus::canceled, $payment->status);
        $this->assertNull($payment->paid_at);
    }

    public function test_payment_can_transition_from_pending_to_overdue(): void
    {
        $payment = Payment::factory()->pending()->create([
            'due_date' => now()->subDays(5),
        ]);

        $payment->update([
            'status' => PaymentStatus::overdue->value,
        ]);

        $this->assertEquals(PaymentStatus::overdue, $payment->status);
    }

    public function test_payment_paid_at_is_set_when_status_changes_to_paid(): void
    {
        $payment = Payment::factory()->pending()->create([
            'paid_at' => null,
        ]);

        $payment->update([
            'status' => PaymentStatus::paid->value,
            'paid_at' => now(),
        ]);

        $this->assertNotNull($payment->paid_at);
    }

    public function test_payment_scope_by_status_filters_correctly(): void
    {
        Payment::factory()->pending()->count(2)->create();
        Payment::factory()->paid()->count(3)->create();
        Payment::factory()->canceled()->count(1)->create();

        $pendingPayments = Payment::byStatus(PaymentStatus::pending)->get();
        $paidPayments = Payment::byStatus(PaymentStatus::paid)->get();

        $this->assertCount(2, $pendingPayments);
        $this->assertCount(3, $paidPayments);
    }

    public function test_payment_scope_pending_filters_correctly(): void
    {
        Payment::factory()->pending()->count(2)->create();
        Payment::factory()->paid()->count(1)->create();

        $pendingPayments = Payment::pending()->get();

        $this->assertCount(2, $pendingPayments);
    }

    public function test_payment_scope_paid_filters_correctly(): void
    {
        Payment::factory()->pending()->count(2)->create();
        Payment::factory()->paid()->count(3)->create();

        $paidPayments = Payment::paid()->get();

        $this->assertCount(3, $paidPayments);
    }

    public function test_payment_scope_overdue_includes_overdue_status(): void
    {
        Payment::factory()->overdue()->count(2)->create();
        Payment::factory()->pending()->create([
            'due_date' => now()->subDays(5),
        ]);
        Payment::factory()->paid()->count(1)->create();

        $overduePayments = Payment::overdue()->get();

        $this->assertGreaterThanOrEqual(2, $overduePayments->count());
    }
}
