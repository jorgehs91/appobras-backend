<?php

namespace Tests\Unit;

use App\Enums\ContractStatus;
use App\Models\Contract;
use App\Models\Contractor;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_contract_can_be_created_with_valid_data(): void
    {
        $contractor = Contractor::factory()->create();
        $project = Project::factory()->create();

        $contract = Contract::factory()->create([
            'contractor_id' => $contractor->id,
            'project_id' => $project->id,
            'value' => 50000.00,
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => ContractStatus::draft,
        ]);

        $this->assertDatabaseHas('contracts', [
            'id' => $contract->id,
            'contractor_id' => $contractor->id,
            'project_id' => $project->id,
            'value' => 50000.00,
            'status' => ContractStatus::draft->value,
        ]);
    }

    public function test_contract_has_contractor_relationship(): void
    {
        $contractor = Contractor::factory()->create();
        $contract = Contract::factory()->create([
            'contractor_id' => $contractor->id,
        ]);

        $this->assertEquals($contractor->id, $contract->contractor->id);
    }

    public function test_contract_has_project_relationship(): void
    {
        $project = Project::factory()->create();
        $contract = Contract::factory()->create([
            'project_id' => $project->id,
        ]);

        $this->assertEquals($project->id, $contract->project->id);
    }

    public function test_contractor_has_contracts_relationship(): void
    {
        $contractor = Contractor::factory()->create();
        Contract::factory()->count(3)->create([
            'contractor_id' => $contractor->id,
        ]);

        $this->assertCount(3, $contractor->contracts);
    }

    public function test_project_has_contracts_relationship(): void
    {
        $project = Project::factory()->create();
        Contract::factory()->count(2)->create([
            'project_id' => $project->id,
        ]);

        $this->assertCount(2, $project->contracts);
    }

    public function test_contract_uses_soft_deletes(): void
    {
        $contract = Contract::factory()->create();
        $contract->delete();

        $this->assertSoftDeleted('contracts', [
            'id' => $contract->id,
        ]);
    }

    public function test_contract_has_work_orders_relationship(): void
    {
        $contract = Contract::factory()->create();
        $workOrder = \App\Models\WorkOrder::factory()->create([
            'contract_id' => $contract->id,
        ]);

        $this->assertCount(1, $contract->workOrders);
        $this->assertEquals($workOrder->id, $contract->workOrders->first()->id);
    }

    public function test_contract_status_is_casted_to_enum(): void
    {
        $contract = Contract::factory()->create([
            'status' => ContractStatus::active,
        ]);

        $this->assertInstanceOf(ContractStatus::class, $contract->status);
        $this->assertEquals(ContractStatus::active, $contract->status);
    }

    public function test_contract_value_is_casted_to_decimal(): void
    {
        $contract = Contract::factory()->create([
            'value' => 12345.67,
        ]);

        $this->assertIsNumeric($contract->value);
        $this->assertEquals('12345.67', (string) $contract->value);
    }

    public function test_contract_dates_are_casted_to_date(): void
    {
        $contract = Contract::factory()->create([
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $contract->start_date);
        $this->assertInstanceOf(\Carbon\Carbon::class, $contract->end_date);
    }

    public function test_contract_has_audit_fields(): void
    {
        $user = User::factory()->create();
        $contractor = Contractor::factory()->create();
        $project = Project::factory()->create();
        
        // Simulate authentication for AuditTrait
        auth()->login($user);

        $contract = Contract::create([
            'contractor_id' => $contractor->id,
            'project_id' => $project->id,
            'value' => 10000.00,
            'start_date' => '2026-01-01',
            'status' => ContractStatus::draft,
        ]);

        $this->assertEquals($user->id, $contract->created_by);
        
        // Update to trigger updated_by
        $contract->update(['value' => 15000.00]);
        $contract->refresh();
        
        $this->assertEquals($user->id, $contract->updated_by);
        
        auth()->logout();
    }
}

