<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Phase;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}

