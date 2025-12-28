<?php

namespace Tests\Unit;

use App\Models\Company;
use App\Models\Phase;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskDependency;
use App\Services\TaskDependencyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskDependencyServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TaskDependencyService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TaskDependencyService();
    }

    public function test_can_add_dependency_returns_true_for_valid_dependency(): void
    {
        $company = Company::factory()->create();
        $project = Project::factory()->create(['company_id' => $company->id]);
        $phase = Phase::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
        ]);

        $task1 = Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'phase_id' => $phase->id,
        ]);

        $task2 = Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'phase_id' => $phase->id,
        ]);

        $this->assertTrue($this->service->canAddDependency($task1->id, $task2->id));
    }

    public function test_can_add_dependency_returns_false_for_self_loop(): void
    {
        $company = Company::factory()->create();
        $project = Project::factory()->create(['company_id' => $company->id]);
        $phase = Phase::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
        ]);

        $task = Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'phase_id' => $phase->id,
        ]);

        $this->assertFalse($this->service->canAddDependency($task->id, $task->id));
    }

    public function test_detect_cycle_on_add_returns_cycle_path_for_self_loop(): void
    {
        $company = Company::factory()->create();
        $project = Project::factory()->create(['company_id' => $company->id]);
        $phase = Phase::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
        ]);

        $task = Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'phase_id' => $phase->id,
        ]);

        $cycle = $this->service->detectCycleOnAdd($task->id, $task->id);

        $this->assertNotNull($cycle);
        $this->assertEquals([$task->id, $task->id], $cycle);
    }

    public function test_detect_cycle_on_add_returns_cycle_for_simple_two_node_cycle(): void
    {
        $company = Company::factory()->create();
        $project = Project::factory()->create(['company_id' => $company->id]);
        $phase = Phase::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
        ]);

        $task1 = Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'phase_id' => $phase->id,
        ]);

        $task2 = Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'phase_id' => $phase->id,
        ]);

        // Create dependency: task1 -> task2
        TaskDependency::create([
            'task_id' => $task1->id,
            'depends_on_task_id' => $task2->id,
        ]);

        // Try to create dependency: task2 -> task1 (would create cycle)
        $cycle = $this->service->detectCycleOnAdd($task2->id, $task1->id);

        $this->assertNotNull($cycle);
        $this->assertCount(3, $cycle); // task2 -> task1 -> task2
        $this->assertEquals($task2->id, $cycle[0]);
        $this->assertEquals($task1->id, $cycle[1]);
    }

    public function test_detect_cycle_on_add_returns_cycle_for_multi_node_cycle(): void
    {
        $company = Company::factory()->create();
        $project = Project::factory()->create(['company_id' => $company->id]);
        $phase = Phase::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
        ]);

        $task1 = Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'phase_id' => $phase->id,
        ]);

        $task2 = Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'phase_id' => $phase->id,
        ]);

        $task3 = Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'phase_id' => $phase->id,
        ]);

        // Create chain: task1 -> task2 -> task3
        TaskDependency::create([
            'task_id' => $task1->id,
            'depends_on_task_id' => $task2->id,
        ]);

        TaskDependency::create([
            'task_id' => $task2->id,
            'depends_on_task_id' => $task3->id,
        ]);

        // Try to create: task3 -> task1 (would create cycle: task1 -> task2 -> task3 -> task1)
        $cycle = $this->service->detectCycleOnAdd($task3->id, $task1->id);

        $this->assertNotNull($cycle);
        $this->assertCount(4, $cycle); // task3 -> task1 -> task2 -> task3
        $this->assertEquals($task3->id, $cycle[0]);
    }

    public function test_detect_cycle_on_add_returns_null_for_acyclic_chain(): void
    {
        $company = Company::factory()->create();
        $project = Project::factory()->create(['company_id' => $company->id]);
        $phase = Phase::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
        ]);

        $task1 = Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'phase_id' => $phase->id,
        ]);

        $task2 = Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'phase_id' => $phase->id,
        ]);

        $task3 = Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'phase_id' => $phase->id,
        ]);

        // Create chain: task1 -> task2 -> task3
        TaskDependency::create([
            'task_id' => $task1->id,
            'depends_on_task_id' => $task2->id,
        ]);

        TaskDependency::create([
            'task_id' => $task2->id,
            'depends_on_task_id' => $task3->id,
        ]);

        // Try to add: task4 -> task1 (should be OK, no cycle)
        $task4 = Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'phase_id' => $phase->id,
        ]);

        $cycle = $this->service->detectCycleOnAdd($task4->id, $task1->id);

        $this->assertNull($cycle);
    }

    public function test_detect_cycle_on_add_ignores_soft_deleted_dependencies(): void
    {
        $company = Company::factory()->create();
        $project = Project::factory()->create(['company_id' => $company->id]);
        $phase = Phase::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
        ]);

        $task1 = Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'phase_id' => $phase->id,
        ]);

        $task2 = Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'phase_id' => $phase->id,
        ]);

        // Create and soft-delete dependency: task1 -> task2
        $dependency = TaskDependency::create([
            'task_id' => $task1->id,
            'depends_on_task_id' => $task2->id,
        ]);

        $dependency->delete(); // Soft delete

        // Try to create: task2 -> task1 (should be OK, deleted dependency is ignored)
        $cycle = $this->service->detectCycleOnAdd($task2->id, $task1->id);

        $this->assertNull($cycle);
    }

    public function test_detect_cycle_on_add_returns_null_for_cross_project_dependency(): void
    {
        $company = Company::factory()->create();
        $project1 = Project::factory()->create(['company_id' => $company->id]);
        $project2 = Project::factory()->create(['company_id' => $company->id]);
        $phase1 = Phase::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project1->id,
        ]);
        $phase2 = Phase::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project2->id,
        ]);

        $task1 = Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project1->id,
            'phase_id' => $phase1->id,
        ]);

        $task2 = Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project2->id,
            'phase_id' => $phase2->id,
        ]);

        // Cross-project dependency attempt should return null (validation will handle rejection)
        $cycle = $this->service->detectCycleOnAdd($task1->id, $task2->id);

        $this->assertNull($cycle);
    }
}

