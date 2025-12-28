<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Phase;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskDependency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskDependencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_task_dependency(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id, ['role' => 'Manager']);

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

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->postJson("/api/v1/projects/{$project->id}/task-dependencies", [
                'task_id' => $task1->id,
                'depends_on_task_id' => $task2->id,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.task_id', $task1->id)
            ->assertJsonPath('data.depends_on_task_id', $task2->id);

        $this->assertDatabaseHas('task_dependencies', [
            'task_id' => $task1->id,
            'depends_on_task_id' => $task2->id,
        ]);
    }

    public function test_cannot_create_self_loop_dependency(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id, ['role' => 'Manager']);

        $phase = Phase::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
        ]);

        $task = Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'phase_id' => $phase->id,
        ]);

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->postJson("/api/v1/projects/{$project->id}/task-dependencies", [
                'task_id' => $task->id,
                'depends_on_task_id' => $task->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['depends_on_task_id']);
    }

    public function test_cannot_create_cycle_dependency(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id, ['role' => 'Manager']);

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

        // Try to create cycle: task2 -> task1
        $response = $this->withHeader('X-Company-Id', $company->id)
            ->postJson("/api/v1/projects/{$project->id}/task-dependencies", [
                'task_id' => $task2->id,
                'depends_on_task_id' => $task1->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['depends_on_task_id'])
            ->assertJsonPath('errors.depends_on_task_id.0', function ($message) {
                return str_contains($message, 'ciclo');
            });
    }

    public function test_can_create_bulk_dependencies(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id, ['role' => 'Manager']);

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

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->postJson("/api/v1/projects/{$project->id}/task-dependencies/bulk", [
                'dependencies' => [
                    [
                        'task_id' => $task1->id,
                        'depends_on_task_id' => $task2->id,
                    ],
                    [
                        'task_id' => $task2->id,
                        'depends_on_task_id' => $task3->id,
                    ],
                ],
            ]);

        $response->assertStatus(201)
            ->assertJsonCount(2, 'data');

        $this->assertDatabaseHas('task_dependencies', [
            'task_id' => $task1->id,
            'depends_on_task_id' => $task2->id,
        ]);

        $this->assertDatabaseHas('task_dependencies', [
            'task_id' => $task2->id,
            'depends_on_task_id' => $task3->id,
        ]);
    }

    public function test_bulk_create_rolls_back_on_cycle_detection(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id, ['role' => 'Manager']);

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

        // Create existing dependency: task1 -> task2
        TaskDependency::create([
            'task_id' => $task1->id,
            'depends_on_task_id' => $task2->id,
        ]);

        // Try bulk create with cycle (task2 -> task1)
        $response = $this->withHeader('X-Company-Id', $company->id)
            ->postJson("/api/v1/projects/{$project->id}/task-dependencies/bulk", [
                'dependencies' => [
                    [
                        'task_id' => $task2->id,
                        'depends_on_task_id' => $task1->id, // This creates a cycle
                    ],
                ],
            ]);

        $response->assertStatus(422);

        // Verify no new dependency was created (transaction rolled back)
        $this->assertDatabaseCount('task_dependencies', 1);
    }

    public function test_can_update_task_dependency(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id, ['role' => 'Manager']);

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

        $dependency = TaskDependency::create([
            'task_id' => $task1->id,
            'depends_on_task_id' => $task2->id,
        ]);

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->putJson("/api/v1/task-dependencies/{$dependency->id}", [
                'depends_on_task_id' => $task3->id,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.depends_on_task_id', $task3->id);

        $this->assertDatabaseHas('task_dependencies', [
            'id' => $dependency->id,
            'task_id' => $task1->id,
            'depends_on_task_id' => $task3->id,
        ]);
    }

    public function test_can_delete_task_dependency(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id, ['role' => 'Manager']);

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

        $dependency = TaskDependency::create([
            'task_id' => $task1->id,
            'depends_on_task_id' => $task2->id,
        ]);

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->deleteJson("/api/v1/task-dependencies/{$dependency->id}");

        $response->assertStatus(204);

        $this->assertSoftDeleted('task_dependencies', [
            'id' => $dependency->id,
        ]);
    }

    public function test_task_dependency_model_has_relationships(): void
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

        $dependency = TaskDependency::create([
            'task_id' => $task1->id,
            'depends_on_task_id' => $task2->id,
        ]);

        $this->assertNotNull($dependency->task);
        $this->assertEquals($task1->id, $dependency->task->id);
        $this->assertNotNull($dependency->dependsOnTask);
        $this->assertEquals($task2->id, $dependency->dependsOnTask->id);
    }

    public function test_task_observer_validates_date_consistency_with_predecessor(): void
    {
        $company = Company::factory()->create();
        $project = Project::factory()->create(['company_id' => $company->id]);
        $phase = Phase::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
        ]);

        $predecessor = Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'phase_id' => $phase->id,
            'planned_start_at' => now()->addDays(1),
            'planned_end_at' => now()->addDays(5),
        ]);

        $task = Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'phase_id' => $phase->id,
            'planned_start_at' => now()->addDays(6), // Initially valid: starts after predecessor finishes
        ]);

        // Create dependency: task depends on predecessor
        TaskDependency::create([
            'task_id' => $task->id,
            'depends_on_task_id' => $predecessor->id,
        ]);

        // Try to update task start date to before predecessor finish date
        $task->planned_start_at = now()->addDays(2); // Before predecessor's end (day 5)

        try {
            $task->save();
            $this->fail('Expected ValidationException was not thrown');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('planned_start_at', $e->errors());
        }
    }

    public function test_task_observer_allows_valid_date_when_predecessor_finishes_first(): void
    {
        $company = Company::factory()->create();
        $project = Project::factory()->create(['company_id' => $company->id]);
        $phase = Phase::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
        ]);

        $predecessor = Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'phase_id' => $phase->id,
            'planned_start_at' => now()->addDays(1),
            'planned_end_at' => now()->addDays(5),
        ]);

        $task = Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'phase_id' => $phase->id,
            'planned_start_at' => now()->addDays(6), // Starts after predecessor finishes
        ]);

        // Create dependency: task depends on predecessor
        TaskDependency::create([
            'task_id' => $task->id,
            'depends_on_task_id' => $predecessor->id,
        ]);

        // Should be OK - task starts after predecessor finishes
        $task->planned_start_at = now()->addDays(6);
        $task->save();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
        ]);
    }

    public function test_task_observer_ignores_soft_deleted_dependencies(): void
    {
        $company = Company::factory()->create();
        $project = Project::factory()->create(['company_id' => $company->id]);
        $phase = Phase::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
        ]);

        $predecessor = Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'phase_id' => $phase->id,
            'planned_start_at' => now()->addDays(1),
            'planned_end_at' => now()->addDays(5),
        ]);

        $task = Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'phase_id' => $phase->id,
            'planned_start_at' => now()->addDays(3),
        ]);

        // Create and soft-delete dependency
        $dependency = TaskDependency::create([
            'task_id' => $task->id,
            'depends_on_task_id' => $predecessor->id,
        ]);

        $dependency->delete(); // Soft delete

        // Should be OK - deleted dependency is ignored
        $task->planned_start_at = now()->addDays(2); // Before predecessor's end, but dependency is deleted
        $task->save();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
        ]);
    }
}

