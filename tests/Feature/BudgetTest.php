<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\Company;
use App\Models\CostItem;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BudgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_budget(): void
    {
        $company = Company::query()->create(['name' => 'Test Company']);
        $project = Project::factory()->create(['company_id' => $company->id]);

        $budget = Budget::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'total_planned' => 100000.00,
        ]);

        $this->assertDatabaseHas('budgets', [
            'id' => $budget->id,
            'company_id' => $company->id,
            'project_id' => $project->id,
            'total_planned' => 100000.00,
        ]);
    }

    public function test_budget_has_project_relationship(): void
    {
        $company = Company::query()->create(['name' => 'Test Company']);
        $project = Project::factory()->create(['company_id' => $company->id]);
        $budget = Budget::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
        ]);

        $this->assertInstanceOf(Project::class, $budget->project);
        $this->assertEquals($project->id, $budget->project->id);
    }

    public function test_budget_has_cost_items_relationship(): void
    {
        $company = Company::query()->create(['name' => 'Test Company']);
        $project = Project::factory()->create(['company_id' => $company->id]);
        $budget = Budget::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'total_planned' => 100000.00,
        ]);

        $costItem1 = CostItem::factory()->create([
            'budget_id' => $budget->id,
            'planned_amount' => 30000.00,
        ]);

        $costItem2 = CostItem::factory()->create([
            'budget_id' => $budget->id,
            'planned_amount' => 20000.00,
        ]);

        $this->assertCount(2, $budget->costItems);
        $this->assertTrue($budget->costItems->contains($costItem1));
        $this->assertTrue($budget->costItems->contains($costItem2));
    }

    public function test_can_create_cost_item_within_budget_limit(): void
    {
        $company = Company::query()->create(['name' => 'Test Company']);
        $project = Project::factory()->create(['company_id' => $company->id]);
        $budget = Budget::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'total_planned' => 100000.00,
        ]);

        $costItem = CostItem::factory()->create([
            'budget_id' => $budget->id,
            'planned_amount' => 50000.00,
        ]);

        $this->assertDatabaseHas('cost_items', [
            'id' => $costItem->id,
            'budget_id' => $budget->id,
            'planned_amount' => 50000.00,
        ]);
    }

    public function test_cannot_create_cost_item_exceeding_budget_total(): void
    {
        $company = Company::query()->create(['name' => 'Test Company']);
        $project = Project::factory()->create(['company_id' => $company->id]);
        $budget = Budget::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'total_planned' => 100000.00,
        ]);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('excede o total planejado do orçamento');

        CostItem::factory()->create([
            'budget_id' => $budget->id,
            'planned_amount' => 150000.00,
        ]);
    }

    public function test_cannot_create_cost_items_that_sum_exceeds_budget_total(): void
    {
        $company = Company::query()->create(['name' => 'Test Company']);
        $project = Project::factory()->create(['company_id' => $company->id]);
        $budget = Budget::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'total_planned' => 100000.00,
        ]);

        // Create first cost item
        CostItem::factory()->create([
            'budget_id' => $budget->id,
            'planned_amount' => 60000.00,
        ]);

        // Try to create second cost item that would exceed total
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('excede o total planejado do orçamento');

        CostItem::factory()->create([
            'budget_id' => $budget->id,
            'planned_amount' => 50000.00, // 60000 + 50000 = 110000 > 100000
        ]);
    }

    public function test_can_update_cost_item_within_budget_limit(): void
    {
        $company = Company::query()->create(['name' => 'Test Company']);
        $project = Project::factory()->create(['company_id' => $company->id]);
        $budget = Budget::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'total_planned' => 100000.00,
        ]);

        $costItem1 = CostItem::factory()->create([
            'budget_id' => $budget->id,
            'planned_amount' => 30000.00,
        ]);

        $costItem2 = CostItem::factory()->create([
            'budget_id' => $budget->id,
            'planned_amount' => 20000.00,
        ]);

        // Update costItem1 to a valid amount
        $costItem1->update(['planned_amount' => 40000.00]);

        $this->assertDatabaseHas('cost_items', [
            'id' => $costItem1->id,
            'planned_amount' => 40000.00,
        ]);
    }

    public function test_cannot_update_cost_item_to_exceed_budget_total(): void
    {
        $company = Company::query()->create(['name' => 'Test Company']);
        $project = Project::factory()->create(['company_id' => $company->id]);
        $budget = Budget::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'total_planned' => 100000.00,
        ]);

        $costItem1 = CostItem::factory()->create([
            'budget_id' => $budget->id,
            'planned_amount' => 30000.00,
        ]);

        $costItem2 = CostItem::factory()->create([
            'budget_id' => $budget->id,
            'planned_amount' => 20000.00,
        ]);

        // Try to update costItem1 to exceed total
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('excede o total planejado do orçamento');

        $costItem1->update(['planned_amount' => 80000.00]); // 80000 + 20000 = 100000 (ok)
        // Actually, let's make it exceed: 80000 + 20000 = 100000, but if we try 90000:
        $costItem1->refresh();
        $costItem1->update(['planned_amount' => 90000.00]); // 90000 + 20000 = 110000 > 100000
    }

    public function test_cost_item_excludes_soft_deleted_items_from_validation(): void
    {
        $company = Company::query()->create(['name' => 'Test Company']);
        $project = Project::factory()->create(['company_id' => $company->id]);
        $budget = Budget::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'total_planned' => 100000.00,
        ]);

        $costItem1 = CostItem::factory()->create([
            'budget_id' => $budget->id,
            'planned_amount' => 60000.00,
        ]);

        $costItem2 = CostItem::factory()->create([
            'budget_id' => $budget->id,
            'planned_amount' => 40000.00,
        ]);

        // Soft delete costItem2
        $costItem2->delete();

        // Now we should be able to create a new cost item up to 40000 (100000 - 60000)
        // since costItem2 is deleted and shouldn't be counted
        $costItem3 = CostItem::factory()->create([
            'budget_id' => $budget->id,
            'planned_amount' => 40000.00, // 60000 + 40000 = 100000 (ok, costItem2 is deleted)
        ]);

        $this->assertDatabaseHas('cost_items', [
            'id' => $costItem3->id,
            'planned_amount' => 40000.00,
        ]);

        // Verify that costItem2 is soft deleted and not counted
        $this->assertSoftDeleted('cost_items', [
            'id' => $costItem2->id,
        ]);
    }

    public function test_budget_uses_audit_trait(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $user->companies()->attach($company->id);
        $project = Project::factory()->create(['company_id' => $company->id]);

        $this->actingAs($user);

        $budget = Budget::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
        ]);

        $this->assertNotNull($budget->created_by);
        $this->assertEquals($user->id, $budget->created_by);

        $budget->update(['total_planned' => 200000.00]);
        $budget->refresh();

        $this->assertNotNull($budget->updated_by);
        $this->assertEquals($user->id, $budget->updated_by);
    }

    public function test_cost_item_uses_audit_trait(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $user->companies()->attach($company->id);
        $project = Project::factory()->create(['company_id' => $company->id]);
        $budget = Budget::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'total_planned' => 100000.00,
        ]);

        $this->actingAs($user);

        $costItem = CostItem::factory()->create([
            'budget_id' => $budget->id,
            'planned_amount' => 50000.00,
        ]);

        $this->assertNotNull($costItem->created_by);
        $this->assertEquals($user->id, $costItem->created_by);

        $costItem->update(['planned_amount' => 60000.00]);
        $costItem->refresh();

        $this->assertNotNull($costItem->updated_by);
        $this->assertEquals($user->id, $costItem->updated_by);
    }
}
