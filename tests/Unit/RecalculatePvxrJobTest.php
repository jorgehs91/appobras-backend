<?php

namespace Tests\Unit;

use App\Enums\ExpenseStatus;
use App\Jobs\RecalculatePvxrJob;
use App\Models\Budget;
use App\Models\Company;
use App\Models\CostItem;
use App\Models\Expense;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class RecalculatePvxrJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_recalcula_pvxr_para_projetos_com_budget(): void
    {
        $company = Company::query()->create(['name' => 'Test Company']);
        
        $project1 = Project::factory()->create(['company_id' => $company->id]);
        $budget1 = Budget::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project1->id,
            'total_planned' => 100000.00,
        ]);
        $costItem1 = CostItem::factory()->create([
            'budget_id' => $budget1->id,
            'planned_amount' => 50000.00,
        ]);

        $project2 = Project::factory()->create(['company_id' => $company->id]);
        $budget2 = Budget::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project2->id,
            'total_planned' => 50000.00,
        ]);
        $costItem2 = CostItem::factory()->create([
            'budget_id' => $budget2->id,
            'planned_amount' => 30000.00,
        ]);

        // Project without budget should be skipped
        $project3 = Project::factory()->create(['company_id' => $company->id]);

        Expense::factory()->create([
            'project_id' => $project1->id,
            'cost_item_id' => $costItem1->id,
            'amount' => 40000.00,
            'status' => ExpenseStatus::approved,
        ]);

        Expense::factory()->create([
            'project_id' => $project2->id,
            'cost_item_id' => $costItem2->id,
            'amount' => 25000.00,
            'status' => ExpenseStatus::approved,
        ]);

        // Clear any existing cache
        Cache::forget("project_pvxr:{$project1->id}");
        Cache::forget("project_pvxr:{$project2->id}");

        $job = new RecalculatePvxrJob();
        $job->handle();

        // Verify cache was created for projects with budgets
        $this->assertTrue(Cache::has("project_pvxr:{$project1->id}"));
        $this->assertTrue(Cache::has("project_pvxr:{$project2->id}"));

        // Verify cache data is correct
        $data1 = Cache::get("project_pvxr:{$project1->id}");
        $this->assertEquals(50000.00, $data1['total']['planned']);
        $this->assertEquals(40000.00, $data1['total']['realized']);

        $data2 = Cache::get("project_pvxr:{$project2->id}");
        $this->assertEquals(30000.00, $data2['total']['planned']);
        $this->assertEquals(25000.00, $data2['total']['realized']);
    }

    public function test_job_atualiza_cache_existente(): void
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

        // Set old cache data
        Cache::put("project_pvxr:{$project->id}", [
            'total' => ['planned' => 0, 'realized' => 0, 'variance' => 0, 'variance_percentage' => 0],
        ], 3600);

        // Create new expense
        Expense::factory()->create([
            'project_id' => $project->id,
            'cost_item_id' => $costItem->id,
            'amount' => 30000.00,
            'status' => ExpenseStatus::approved,
        ]);

        $job = new RecalculatePvxrJob();
        $job->handle();

        // Verify cache was updated with fresh data
        $data = Cache::get("project_pvxr:{$project->id}");
        $this->assertEquals(50000.00, $data['total']['planned']);
        $this->assertEquals(30000.00, $data['total']['realized']);
    }
}

