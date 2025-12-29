<?php

namespace Tests\Feature;

use App\Enums\TaskStatus;
use App\Jobs\SendAlertJob;
use App\Models\Company;
use App\Models\Notification;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AlertGeneratorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    public function test_command_generates_alerts_for_overdue_tasks(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        $project = Project::factory()->create(['company_id' => $company->id]);

        // Create an overdue task
        $overdueTask = Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'assignee_id' => $user->id,
            'due_at' => Carbon::now()->subDays(5),
            'status' => TaskStatus::in_progress,
        ]);

        $this->artisan('alerts:generate')
            ->expectsOutput('Generating alerts...')
            ->expectsOutput('Found 1 users with overdue tasks')
            ->expectsOutput('Found 0 users with near-due tasks')
            ->expectsOutput('Dispatched alert jobs for 1 users')
            ->expectsOutput('Alerts generated successfully.')
            ->assertExitCode(0);

        Queue::assertPushed(SendAlertJob::class, function ($job) use ($user, $overdueTask) {
            return $job->userId === $user->id
                && $job->overdueTasks->contains('id', $overdueTask->id)
                && $job->nearDueTasks->isEmpty();
        });
    }

    public function test_command_generates_alerts_for_near_due_tasks(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        $project = Project::factory()->create(['company_id' => $company->id]);

        // Create a near-due task (due in 2 days)
        $nearDueTask = Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'assignee_id' => $user->id,
            'due_at' => Carbon::now()->addDays(2),
            'status' => TaskStatus::in_progress,
        ]);

        $this->artisan('alerts:generate')
            ->expectsOutput('Generating alerts...')
            ->expectsOutput('Found 0 users with overdue tasks')
            ->expectsOutput('Found 1 users with near-due tasks')
            ->expectsOutput('Dispatched alert jobs for 1 users')
            ->expectsOutput('Alerts generated successfully.')
            ->assertExitCode(0);

        Queue::assertPushed(SendAlertJob::class, function ($job) use ($user, $nearDueTask) {
            return $job->userId === $user->id
                && $job->overdueTasks->isEmpty()
                && $job->nearDueTasks->contains('id', $nearDueTask->id);
        });
    }

    public function test_command_ignores_completed_tasks(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        $project = Project::factory()->create(['company_id' => $company->id]);

        // Create an overdue task that is already done
        Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'assignee_id' => $user->id,
            'due_at' => Carbon::now()->subDays(5),
            'status' => TaskStatus::done,
        ]);

        $this->artisan('alerts:generate')
            ->expectsOutput('Found 0 users with overdue tasks')
            ->expectsOutput('Found 0 users with near-due tasks')
            ->expectsOutput('Dispatched alert jobs for 0 users')
            ->assertExitCode(0);

        Queue::assertNothingPushed();
    }

    public function test_command_ignores_canceled_tasks(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        $project = Project::factory()->create(['company_id' => $company->id]);

        // Create an overdue task that is canceled
        Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'assignee_id' => $user->id,
            'due_at' => Carbon::now()->subDays(5),
            'status' => TaskStatus::canceled,
        ]);

        $this->artisan('alerts:generate')
            ->expectsOutput('Found 0 users with overdue tasks')
            ->expectsOutput('Found 0 users with near-due tasks')
            ->expectsOutput('Dispatched alert jobs for 0 users')
            ->assertExitCode(0);

        Queue::assertNothingPushed();
    }

    public function test_command_ignores_tasks_without_due_date(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        $project = Project::factory()->create(['company_id' => $company->id]);

        // Create a task without due date
        Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'assignee_id' => $user->id,
            'due_at' => null,
            'status' => TaskStatus::in_progress,
        ]);

        $this->artisan('alerts:generate')
            ->expectsOutput('Found 0 users with overdue tasks')
            ->expectsOutput('Found 0 users with near-due tasks')
            ->expectsOutput('Dispatched alert jobs for 0 users')
            ->assertExitCode(0);

        Queue::assertNothingPushed();
    }

    public function test_command_ignores_tasks_without_assignee(): void
    {
        $company = Company::factory()->create();
        $project = Project::factory()->create(['company_id' => $company->id]);

        // Create a task without assignee
        Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'assignee_id' => null,
            'due_at' => Carbon::now()->subDays(5),
            'status' => TaskStatus::in_progress,
        ]);

        $this->artisan('alerts:generate')
            ->expectsOutput('Found 0 users with overdue tasks')
            ->expectsOutput('Found 0 users with near-due tasks')
            ->expectsOutput('Dispatched alert jobs for 0 users')
            ->assertExitCode(0);

        Queue::assertNothingPushed();
    }

    public function test_command_respects_alert_task_days_config(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        $project = Project::factory()->create(['company_id' => $company->id]);

        // Set custom alert days to 5
        config(['app.alert_task_days' => 5]);

        // Create a task due in 4 days (should be included)
        $taskIncluded = Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'assignee_id' => $user->id,
            'due_at' => Carbon::now()->addDays(4),
            'status' => TaskStatus::in_progress,
        ]);

        // Create a task due in 6 days (should be excluded)
        Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'assignee_id' => $user->id,
            'due_at' => Carbon::now()->addDays(6),
            'status' => TaskStatus::in_progress,
        ]);

        $this->artisan('alerts:generate')
            ->expectsOutput('Found 0 users with overdue tasks')
            ->expectsOutput('Found 1 users with near-due tasks')
            ->assertExitCode(0);

        Queue::assertPushed(SendAlertJob::class, function ($job) use ($taskIncluded) {
            return $job->nearDueTasks->contains('id', $taskIncluded->id)
                && $job->nearDueTasks->count() === 1;
        });
    }

    public function test_command_groups_tasks_by_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $company = Company::factory()->create();
        $user1->companies()->attach($company->id);
        $user2->companies()->attach($company->id);
        $project = Project::factory()->create(['company_id' => $company->id]);

        // Create overdue tasks for user1
        $task1 = Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'assignee_id' => $user1->id,
            'due_at' => Carbon::now()->subDays(2),
            'status' => TaskStatus::in_progress,
        ]);

        // Create overdue task for user2
        $task2 = Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'assignee_id' => $user2->id,
            'due_at' => Carbon::now()->subDays(3),
            'status' => TaskStatus::in_progress,
        ]);

        $this->artisan('alerts:generate')
            ->expectsOutput('Found 2 users with overdue tasks')
            ->expectsOutput('Dispatched alert jobs for 2 users')
            ->assertExitCode(0);

        Queue::assertPushed(SendAlertJob::class, 2);

        Queue::assertPushed(SendAlertJob::class, function ($job) use ($user1, $task1) {
            return $job->userId === $user1->id
                && $job->overdueTasks->contains('id', $task1->id);
        });

        Queue::assertPushed(SendAlertJob::class, function ($job) use ($user2, $task2) {
            return $job->userId === $user2->id
                && $job->overdueTasks->contains('id', $task2->id);
        });
    }
}

