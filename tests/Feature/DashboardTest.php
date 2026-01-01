<?php

namespace Tests\Feature;

use App\Enums\ExpenseStatus;
use App\Http\Controllers\DashboardController;
use App\Models\Budget;
use App\Models\Company;
use App\Models\CostItem;
use App\Models\Expense;
use App\Models\Phase;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_stats_returns_correct_structure(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'C1']);
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $project = Project::query()->create([
            'company_id' => $company->id,
            'name' => 'P1',
        ]);
        $user->projects()->attach($project->id, ['role' => 'Viewer']);

        $response = $this->getJson('/api/v1/dashboard/stats', ['X-Company-Id' => $company->id]);

        $response->assertOk()
            ->assertJsonStructure([
                'avg_progress',
                'overdue_tasks_count',
                'upcoming_deliveries_count',
                'total_budget',
                'pvxr_summary' => [
                    'total_planned',
                    'total_realized',
                    'variance',
                    'variance_percentage',
                ],
                'expiring_licenses' => [
                    'expiring_count',
                    'expiring_soon_count',
                    'days_threshold',
                ],
            ]);
    }

    public function test_dashboard_stats_calculates_avg_progress(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'C1']);
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        // Create 2 projects with known progress
        $project1 = Project::query()->create([
            'company_id' => $company->id,
            'name' => 'P1',
        ]);
        $user->projects()->attach($project1->id, ['role' => 'Viewer']);

        $project2 = Project::query()->create([
            'company_id' => $company->id,
            'name' => 'P2',
        ]);
        $user->projects()->attach($project2->id, ['role' => 'Viewer']);

        // Create phases and tasks to generate progress
        $phase1 = Phase::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project1->id,
            'status' => 'active',
        ]);

        // Project 1: 100% done (all tasks done)
        Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project1->id,
            'phase_id' => $phase1->id,
            'status' => 'done',
        ]);

        $phase2 = Phase::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project2->id,
            'status' => 'active',
        ]);

        // Project 2: 50% done (one task in progress)
        Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project2->id,
            'phase_id' => $phase2->id,
            'status' => 'in_progress',
        ]);

        $response = $this->getJson('/api/v1/dashboard/stats', ['X-Company-Id' => $company->id]);

        // Average should be (100 + 50) / 2 = 75
        $response->assertOk()
            ->assertJsonPath('avg_progress', 75);
    }

    public function test_dashboard_stats_counts_overdue_tasks(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'C1']);
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $project = Project::query()->create([
            'company_id' => $company->id,
            'name' => 'P1',
        ]);
        $user->projects()->attach($project->id, ['role' => 'Viewer']);

        $phase = Phase::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'status' => 'active',
        ]);

        // Create 2 overdue tasks (planned_end_at in the past, not done)
        Task::factory()->count(2)->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'phase_id' => $phase->id,
            'status' => 'in_progress',
            'planned_end_at' => now()->subDays(5)->toDateString(),
        ]);

        // Create 1 overdue but done task (should not count)
        Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'phase_id' => $phase->id,
            'status' => 'done',
            'planned_end_at' => now()->subDays(5)->toDateString(),
        ]);

        // Create 1 not overdue task
        Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'phase_id' => $phase->id,
            'status' => 'in_progress',
            'planned_end_at' => now()->addDays(5)->toDateString(),
        ]);

        $response = $this->getJson('/api/v1/dashboard/stats', ['X-Company-Id' => $company->id]);

        $response->assertOk()
            ->assertJsonPath('overdue_tasks_count', 2);
    }

    public function test_dashboard_stats_counts_upcoming_deliveries(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'C1']);
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $project = Project::query()->create([
            'company_id' => $company->id,
            'name' => 'P1',
        ]);
        $user->projects()->attach($project->id, ['role' => 'Viewer']);

        $phase = Phase::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'status' => 'active',
        ]);

        // Create 3 tasks due in the next 7 days
        Task::factory()->count(3)->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'phase_id' => $phase->id,
            'status' => 'in_progress',
            'due_at' => now()->addDays(3)->toDateString(),
        ]);

        // Create 1 task due beyond 7 days (should not count)
        Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'phase_id' => $phase->id,
            'status' => 'in_progress',
            'due_at' => now()->addDays(10)->toDateString(),
        ]);

        // Create 1 task due in 7 days but already done (should not count)
        Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'phase_id' => $phase->id,
            'status' => 'done',
            'due_at' => now()->addDays(3)->toDateString(),
        ]);

        $response = $this->getJson('/api/v1/dashboard/stats', ['X-Company-Id' => $company->id]);

        $response->assertOk()
            ->assertJsonPath('upcoming_deliveries_count', 3);
    }

    public function test_dashboard_stats_sums_total_budget(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'C1']);
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        // Create 3 projects with budgets
        $project1 = Project::query()->create([
            'company_id' => $company->id,
            'name' => 'P1',
            'planned_budget_amount' => 10000.00,
        ]);
        $user->projects()->attach($project1->id, ['role' => 'Viewer']);

        $project2 = Project::query()->create([
            'company_id' => $company->id,
            'name' => 'P2',
            'planned_budget_amount' => 25000.00,
        ]);
        $user->projects()->attach($project2->id, ['role' => 'Viewer']);

        $project3 = Project::query()->create([
            'company_id' => $company->id,
            'name' => 'P3',
            'planned_budget_amount' => 15000.00,
        ]);
        $user->projects()->attach($project3->id, ['role' => 'Viewer']);

        $response = $this->getJson('/api/v1/dashboard/stats', ['X-Company-Id' => $company->id]);

        $response->assertOk();
        
        $this->assertEquals(50000, $response->json('total_budget'));
    }

    public function test_dashboard_stats_filters_by_project_id(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'C1']);
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        // Create 2 projects
        $project1 = Project::query()->create([
            'company_id' => $company->id,
            'name' => 'P1',
            'planned_budget_amount' => 10000.00,
        ]);
        $user->projects()->attach($project1->id, ['role' => 'Viewer']);

        $project2 = Project::query()->create([
            'company_id' => $company->id,
            'name' => 'P2',
            'planned_budget_amount' => 25000.00,
        ]);
        $user->projects()->attach($project2->id, ['role' => 'Viewer']);

        // Filter by project1
        $response = $this->getJson('/api/v1/dashboard/stats?project_id='.$project1->id, ['X-Company-Id' => $company->id]);

        $response->assertOk();
        
        $this->assertEquals(10000, $response->json('total_budget'));
    }

    public function test_dashboard_stats_only_shows_user_projects(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'C1']);
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        // Create a project where user is member
        $userProject = Project::query()->create([
            'company_id' => $company->id,
            'name' => 'User Project',
            'planned_budget_amount' => 10000.00,
        ]);
        $user->projects()->attach($userProject->id, ['role' => 'Viewer']);

        // Create a project where user is NOT a member
        $otherProject = Project::query()->create([
            'company_id' => $company->id,
            'name' => 'Other Project',
            'planned_budget_amount' => 50000.00,
        ]);

        $response = $this->getJson('/api/v1/dashboard/stats', ['X-Company-Id' => $company->id]);

        // Should only count user's project
        $response->assertOk();
        
        $this->assertEquals(10000, $response->json('total_budget'));
    }

    public function test_dashboard_stats_includes_pvxr_summary(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'C1']);
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $project = Project::query()->create([
            'company_id' => $company->id,
            'name' => 'P1',
        ]);
        $user->projects()->attach($project->id, ['role' => 'Viewer']);

        // Create budget with cost items
        $budget = Budget::query()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'total_planned' => 10000.00,
        ]);

        // Create cost items with planned amounts
        $costItem1 = CostItem::query()->create([
            'company_id' => $company->id,
            'budget_id' => $budget->id,
            'name' => 'Item 1',
            'planned_amount' => 5000.00,
            'category' => 'Category A',
        ]);

        $costItem2 = CostItem::query()->create([
            'company_id' => $company->id,
            'budget_id' => $budget->id,
            'name' => 'Item 2',
            'planned_amount' => 3000.00,
            'category' => 'Category B',
        ]);

        // Create approved expenses (realized)
        Expense::query()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'cost_item_id' => $costItem1->id,
            'amount' => 4000.00,
            'date' => now(),
            'status' => ExpenseStatus::approved,
        ]);

        Expense::query()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'cost_item_id' => $costItem2->id,
            'amount' => 2000.00,
            'date' => now(),
            'status' => ExpenseStatus::approved,
        ]);

        $response = $this->getJson('/api/v1/dashboard/stats', ['X-Company-Id' => $company->id]);

        $response->assertOk();

        // Total planned: 5000 + 3000 = 8000
        // Total realized: 4000 + 2000 = 6000
        // Variance: 8000 - 6000 = 2000
        // Variance percentage: (2000 / 8000) * 100 = 25
        $this->assertEquals(8000.0, $response->json('pvxr_summary.total_planned'));
        $this->assertEquals(6000.0, $response->json('pvxr_summary.total_realized'));
        $this->assertEquals(2000.0, $response->json('pvxr_summary.variance'));
        $this->assertEquals(25.0, $response->json('pvxr_summary.variance_percentage'));
    }

    public function test_dashboard_stats_pvxr_summary_returns_zero_when_no_budget(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'C1']);
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $project = Project::query()->create([
            'company_id' => $company->id,
            'name' => 'P1',
        ]);
        $user->projects()->attach($project->id, ['role' => 'Viewer']);

        $response = $this->getJson('/api/v1/dashboard/stats', ['X-Company-Id' => $company->id]);

        $response->assertOk();

        $this->assertEquals(0.0, $response->json('pvxr_summary.total_planned'));
        $this->assertEquals(0.0, $response->json('pvxr_summary.total_realized'));
        $this->assertEquals(0.0, $response->json('pvxr_summary.variance'));
        $this->assertEquals(0.0, $response->json('pvxr_summary.variance_percentage'));
    }

    public function test_dashboard_stats_pvxr_summary_excludes_pending_expenses(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'C1']);
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $project = Project::query()->create([
            'company_id' => $company->id,
            'name' => 'P1',
        ]);
        $user->projects()->attach($project->id, ['role' => 'Viewer']);

        $budget = Budget::query()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'total_planned' => 10000.00,
        ]);

        $costItem = CostItem::query()->create([
            'company_id' => $company->id,
            'budget_id' => $budget->id,
            'name' => 'Item 1',
            'planned_amount' => 5000.00,
            'category' => 'Category A',
        ]);

        // Create approved expense (should be counted)
        Expense::query()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'cost_item_id' => $costItem->id,
            'amount' => 2000.00,
            'date' => now(),
            'status' => ExpenseStatus::approved,
        ]);

        // Create draft expense (should NOT be counted)
        Expense::query()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'cost_item_id' => $costItem->id,
            'amount' => 1000.00,
            'date' => now(),
            'status' => ExpenseStatus::draft,
        ]);

        $response = $this->getJson('/api/v1/dashboard/stats', ['X-Company-Id' => $company->id]);

        $response->assertOk();

        // Only approved expense should be counted
        $this->assertEquals(2000.0, $response->json('pvxr_summary.total_realized'));
    }

    public function test_dashboard_stats_includes_expiring_licenses_placeholder(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'C1']);
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $project = Project::query()->create([
            'company_id' => $company->id,
            'name' => 'P1',
        ]);
        $user->projects()->attach($project->id, ['role' => 'Viewer']);

        $response = $this->getJson('/api/v1/dashboard/stats', ['X-Company-Id' => $company->id]);

        $response->assertOk();

        // Placeholder values (License model not yet implemented)
        $this->assertEquals(0, $response->json('expiring_licenses.expiring_count'));
        $this->assertEquals(0, $response->json('expiring_licenses.expiring_soon_count'));
        $this->assertIsInt($response->json('expiring_licenses.days_threshold'));
        $this->assertGreaterThanOrEqual(0, $response->json('expiring_licenses.days_threshold'));
    }

    public function test_dashboard_stats_pvxr_summary_aggregates_multiple_projects(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'C1']);
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        // Project 1
        $project1 = Project::query()->create([
            'company_id' => $company->id,
            'name' => 'P1',
        ]);
        $user->projects()->attach($project1->id, ['role' => 'Viewer']);

        $budget1 = Budget::query()->create([
            'company_id' => $company->id,
            'project_id' => $project1->id,
            'total_planned' => 10000.00,
        ]);

        $costItem1 = CostItem::query()->create([
            'company_id' => $company->id,
            'budget_id' => $budget1->id,
            'name' => 'Item 1',
            'planned_amount' => 5000.00,
            'category' => 'Category A',
        ]);

        Expense::query()->create([
            'company_id' => $company->id,
            'project_id' => $project1->id,
            'cost_item_id' => $costItem1->id,
            'amount' => 3000.00,
            'date' => now(),
            'status' => ExpenseStatus::approved,
        ]);

        // Project 2
        $project2 = Project::query()->create([
            'company_id' => $company->id,
            'name' => 'P2',
        ]);
        $user->projects()->attach($project2->id, ['role' => 'Viewer']);

        $budget2 = Budget::query()->create([
            'company_id' => $company->id,
            'project_id' => $project2->id,
            'total_planned' => 15000.00,
        ]);

        $costItem2 = CostItem::query()->create([
            'company_id' => $company->id,
            'budget_id' => $budget2->id,
            'name' => 'Item 2',
            'planned_amount' => 7000.00,
            'category' => 'Category B',
        ]);

        Expense::query()->create([
            'company_id' => $company->id,
            'project_id' => $project2->id,
            'cost_item_id' => $costItem2->id,
            'amount' => 5000.00,
            'date' => now(),
            'status' => ExpenseStatus::approved,
        ]);

        $response = $this->getJson('/api/v1/dashboard/stats', ['X-Company-Id' => $company->id]);

        $response->assertOk();

        // Total planned: 5000 + 7000 = 12000
        // Total realized: 3000 + 5000 = 8000
        // Variance: 12000 - 8000 = 4000
        // Variance percentage: (4000 / 12000) * 100 = 33.33
        $this->assertEquals(12000.0, $response->json('pvxr_summary.total_planned'));
        $this->assertEquals(8000.0, $response->json('pvxr_summary.total_realized'));
        $this->assertEquals(4000.0, $response->json('pvxr_summary.variance'));
        $this->assertEqualsWithDelta(33.33, $response->json('pvxr_summary.variance_percentage'), 0.1);
    }

    public function test_dashboard_stats_uses_cache(): void
    {
        Cache::flush();

        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'C1']);
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $project = Project::query()->create([
            'company_id' => $company->id,
            'name' => 'P1',
        ]);
        $user->projects()->attach($project->id, ['role' => 'Viewer']);

        // First request - should calculate and cache
        $response1 = $this->getJson('/api/v1/dashboard/stats', ['X-Company-Id' => $company->id]);
        $response1->assertOk();

        // Verify cache was created
        $cacheKey = "dashboard.stats:user:{$user->id}:company:{$company->id}";
        $this->assertTrue(Cache::has($cacheKey));

        // Second request - should use cache
        $response2 = $this->getJson('/api/v1/dashboard/stats', ['X-Company-Id' => $company->id]);
        $response2->assertOk();

        // Responses should be identical
        $this->assertEquals($response1->json(), $response2->json());
    }

    public function test_dashboard_stats_cache_invalidates_on_expense_change(): void
    {
        Cache::flush();

        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'C1']);
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $project = Project::query()->create([
            'company_id' => $company->id,
            'name' => 'P1',
        ]);
        $user->projects()->attach($project->id, ['role' => 'Viewer']);

        $budget = Budget::query()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'total_planned' => 10000.00,
        ]);

        $costItem = CostItem::query()->create([
            'company_id' => $company->id,
            'budget_id' => $budget->id,
            'name' => 'Item 1',
            'planned_amount' => 5000.00,
            'category' => 'Category A',
        ]);

        // First request - cache created
        $response1 = $this->getJson('/api/v1/dashboard/stats', ['X-Company-Id' => $company->id]);
        $response1->assertOk();
        $initialRealized = $response1->json('pvxr_summary.total_realized');

        // Create an expense - should invalidate cache
        Expense::query()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'cost_item_id' => $costItem->id,
            'amount' => 1000.00,
            'date' => now(),
            'status' => ExpenseStatus::approved,
        ]);

        // Second request - should recalculate (cache invalidated)
        $response2 = $this->getJson('/api/v1/dashboard/stats', ['X-Company-Id' => $company->id]);
        $response2->assertOk();

        // Realized amount should have increased
        $this->assertGreaterThan($initialRealized, $response2->json('pvxr_summary.total_realized'));
    }

    public function test_dashboard_stats_cache_invalidates_on_task_change(): void
    {
        Cache::flush();

        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'C1']);
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $project = Project::query()->create([
            'company_id' => $company->id,
            'name' => 'P1',
        ]);
        $user->projects()->attach($project->id, ['role' => 'Viewer']);

        $phase = Phase::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'status' => 'active',
        ]);

        // First request - cache created
        $response1 = $this->getJson('/api/v1/dashboard/stats', ['X-Company-Id' => $company->id]);
        $response1->assertOk();
        $initialOverdue = $response1->json('overdue_tasks_count');

        // Create an overdue task - should invalidate cache
        Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'phase_id' => $phase->id,
            'status' => 'in_progress',
            'planned_end_at' => now()->subDays(5)->toDateString(),
        ]);

        // Second request - should recalculate (cache invalidated)
        $response2 = $this->getJson('/api/v1/dashboard/stats', ['X-Company-Id' => $company->id]);
        $response2->assertOk();

        // Overdue count should have increased
        $this->assertGreaterThan($initialOverdue, $response2->json('overdue_tasks_count'));
    }

    public function test_dashboard_stats_cache_key_includes_project_filter(): void
    {
        Cache::flush();

        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'C1']);
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $project1 = Project::query()->create([
            'company_id' => $company->id,
            'name' => 'P1',
            'planned_budget_amount' => 10000.00,
        ]);
        $user->projects()->attach($project1->id, ['role' => 'Viewer']);

        $project2 = Project::query()->create([
            'company_id' => $company->id,
            'name' => 'P2',
            'planned_budget_amount' => 20000.00,
        ]);
        $user->projects()->attach($project2->id, ['role' => 'Viewer']);

        // Request without project filter
        $response1 = $this->getJson('/api/v1/dashboard/stats', ['X-Company-Id' => $company->id]);
        $response1->assertOk();
        $totalBudget1 = $response1->json('total_budget');

        // Request with project filter
        $response2 = $this->getJson("/api/v1/dashboard/stats?project_id={$project1->id}", ['X-Company-Id' => $company->id]);
        $response2->assertOk();
        $totalBudget2 = $response2->json('total_budget');

        // Should have different budgets (different cache keys)
        $this->assertNotEquals($totalBudget1, $totalBudget2);
        $this->assertEquals(10000, $totalBudget2);
    }
}

