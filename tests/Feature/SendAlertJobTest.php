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
use Illuminate\Support\Collection;
use Tests\TestCase;

class SendAlertJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_creates_notifications_for_overdue_tasks(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        $project = Project::factory()->create(['company_id' => $company->id]);

        $overdueTask = Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'assignee_id' => $user->id,
            'due_at' => Carbon::now()->subDays(5),
            'status' => TaskStatus::in_progress,
        ]);

        $job = new SendAlertJob(
            $user->id,
            collect([$overdueTask]),
            collect([]),
            []
        );

        $job->handle();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'notifiable_id' => $overdueTask->id,
            'notifiable_type' => Task::class,
            'type' => 'task.overdue',
        ]);

        $notification = Notification::where('user_id', $user->id)
            ->where('type', 'task.overdue')
            ->first();

        $this->assertNotNull($notification);
        $this->assertEquals($overdueTask->id, $notification->data['task_id']);
        $this->assertEquals($overdueTask->title, $notification->data['task_title']);
        $this->assertEquals(['database'], $notification->channels);
    }

    public function test_job_creates_notifications_for_near_due_tasks(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        $project = Project::factory()->create(['company_id' => $company->id]);

        $nearDueTask = Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'assignee_id' => $user->id,
            'due_at' => Carbon::now()->addDays(2),
            'status' => TaskStatus::in_progress,
        ]);

        $job = new SendAlertJob(
            $user->id,
            collect([]),
            collect([$nearDueTask]),
            []
        );

        $job->handle();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'notifiable_id' => $nearDueTask->id,
            'notifiable_type' => Task::class,
            'type' => 'task.near_due',
        ]);

        $notification = Notification::where('user_id', $user->id)
            ->where('type', 'task.near_due')
            ->first();

        $this->assertNotNull($notification);
        $this->assertEquals($nearDueTask->id, $notification->data['task_id']);
        $this->assertEquals($nearDueTask->title, $notification->data['task_title']);
        $this->assertEquals($project->id, $notification->data['project_id']);
    }

    public function test_job_creates_notifications_for_multiple_tasks(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        $project = Project::factory()->create(['company_id' => $company->id]);

        $overdueTask1 = Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'assignee_id' => $user->id,
            'due_at' => Carbon::now()->subDays(5),
            'status' => TaskStatus::in_progress,
        ]);

        $overdueTask2 = Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'assignee_id' => $user->id,
            'due_at' => Carbon::now()->subDays(3),
            'status' => TaskStatus::in_progress,
        ]);

        $nearDueTask = Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'assignee_id' => $user->id,
            'due_at' => Carbon::now()->addDays(2),
            'status' => TaskStatus::in_progress,
        ]);

        $job = new SendAlertJob(
            $user->id,
            collect([$overdueTask1, $overdueTask2]),
            collect([$nearDueTask]),
            []
        );

        $job->handle();

        $this->assertDatabaseCount('notifications', 3);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'notifiable_id' => $overdueTask1->id,
            'type' => 'task.overdue',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'notifiable_id' => $overdueTask2->id,
            'type' => 'task.overdue',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'notifiable_id' => $nearDueTask->id,
            'type' => 'task.near_due',
        ]);
    }

    public function test_job_does_not_create_notifications_if_user_not_found(): void
    {
        $nonExistentUserId = 99999;

        $job = new SendAlertJob(
            $nonExistentUserId,
            collect([]),
            collect([]),
            []
        );

        $job->handle();

        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_job_does_not_create_notifications_if_no_tasks(): void
    {
        $user = User::factory()->create();

        $job = new SendAlertJob(
            $user->id,
            collect([]),
            collect([]),
            []
        );

        $job->handle();

        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_notification_data_includes_project_information(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        $project = Project::factory()->create([
            'company_id' => $company->id,
            'name' => 'Test Project',
        ]);

        $task = Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'assignee_id' => $user->id,
            'due_at' => Carbon::now()->subDays(5),
            'status' => TaskStatus::in_progress,
        ]);

        $job = new SendAlertJob(
            $user->id,
            collect([$task]),
            collect([]),
            []
        );

        $job->handle();

        $notification = Notification::where('user_id', $user->id)->first();

        $this->assertNotNull($notification);
        $this->assertEquals($project->id, $notification->data['project_id']);
        $this->assertEquals($project->name, $notification->data['project_name']);
        $this->assertEquals($task->due_at->toDateString(), $notification->data['due_at']);
    }
}

