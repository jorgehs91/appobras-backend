<?php

namespace Tests\Unit;

use App\Enums\TaskStatus;
use App\Models\Company;
use App\Models\Phase;
use App\Models\Project;
use App\Models\Task;
use App\Services\TaskBulkService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TaskBulkServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TaskBulkService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TaskBulkService();
    }

    public function test_bulk_update_updates_multiple_tasks_successfully(): void
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
            'status' => 'backlog',
            'order_in_phase' => 1,
        ]);

        $task2 = Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'phase_id' => $phase->id,
            'status' => 'backlog',
            'order_in_phase' => 2,
        ]);

        $tasksData = [
            [
                'id' => $task1->id,
                'status' => 'in_progress',
                'position' => 1.1,
            ],
            [
                'id' => $task2->id,
                'status' => 'done',
                'position' => 1.2,
            ],
        ];

        $updatedTasks = $this->service->bulkUpdate($tasksData, $project);

        $this->assertCount(2, $updatedTasks);
        $this->assertEquals(TaskStatus::in_progress, $updatedTasks[0]->status);
        $this->assertEquals(1.1, $updatedTasks[0]->order_in_phase);
        $this->assertEquals(TaskStatus::done, $updatedTasks[1]->status);
        $this->assertEquals(1.2, $updatedTasks[1]->order_in_phase);
    }

    public function test_bulk_update_maps_position_to_order_in_phase(): void
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
            'order_in_phase' => 1,
        ]);

        $tasksData = [
            [
                'id' => $task->id,
                'position' => 2.5,
            ],
        ];

        $updatedTasks = $this->service->bulkUpdate($tasksData, $project);

        $this->assertCount(1, $updatedTasks);
        $this->assertEquals(2.5, $updatedTasks[0]->order_in_phase);
    }

    public function test_bulk_update_throws_exception_when_task_not_found(): void
    {
        $company = Company::factory()->create();
        $project = Project::factory()->create(['company_id' => $company->id]);

        $tasksData = [
            [
                'id' => 99999, // Non-existent task
                'status' => 'in_progress',
            ],
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Uma ou mais tarefas não foram encontradas ou não pertencem ao projeto.');

        $this->service->bulkUpdate($tasksData, $project);
    }

    public function test_bulk_update_throws_exception_when_task_belongs_to_different_project(): void
    {
        $company = Company::factory()->create();
        $project1 = Project::factory()->create(['company_id' => $company->id]);
        $project2 = Project::factory()->create(['company_id' => $company->id]);
        $phase = Phase::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project2->id,
        ]);

        $task = Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project2->id,
            'phase_id' => $phase->id,
        ]);

        $tasksData = [
            [
                'id' => $task->id,
                'status' => 'in_progress',
            ],
        ];

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Uma ou mais tarefas não foram encontradas ou não pertencem ao projeto.');

        $this->service->bulkUpdate($tasksData, $project1);
    }

    public function test_bulk_update_rolls_back_on_exception(): void
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
            'status' => 'backlog',
        ]);

        $tasksData = [
            [
                'id' => $task1->id,
                'status' => 'in_progress',
            ],
            [
                'id' => 99999, // This will cause exception
                'status' => 'done',
            ],
        ];

        try {
            $this->service->bulkUpdate($tasksData, $project);
            $this->fail('Expected ValidationException was not thrown');
        } catch (ValidationException $e) {
            // Expected
        }

        // Verify task1 was NOT updated (transaction rolled back)
        $task1->refresh();
        $this->assertEquals(TaskStatus::backlog, $task1->status);
    }

    public function test_bulk_update_returns_tasks_with_relationships_loaded(): void
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

        $tasksData = [
            [
                'id' => $task->id,
                'status' => 'in_progress',
            ],
        ];

        $updatedTasks = $this->service->bulkUpdate($tasksData, $project);

        $this->assertCount(1, $updatedTasks);
        $this->assertTrue($updatedTasks[0]->relationLoaded('phase'));
        $this->assertTrue($updatedTasks[0]->relationLoaded('assignee'));
        $this->assertTrue($updatedTasks[0]->relationLoaded('contractor'));
    }

    public function test_bulk_update_preserves_task_order_from_input(): void
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

        // Update in reverse order
        $tasksData = [
            ['id' => $task3->id, 'status' => 'done'],
            ['id' => $task2->id, 'status' => 'in_progress'],
            ['id' => $task1->id, 'status' => 'backlog'],
        ];

        $updatedTasks = $this->service->bulkUpdate($tasksData, $project);

        $this->assertCount(3, $updatedTasks);
        $this->assertEquals($task3->id, $updatedTasks[0]->id);
        $this->assertEquals($task2->id, $updatedTasks[1]->id);
        $this->assertEquals($task1->id, $updatedTasks[2]->id);
    }
}

