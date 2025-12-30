<?php

namespace Tests\Feature;

use App\Enums\TaskStatus;
use App\Jobs\SendAlertJob;
use App\Mail\AlertMailable;
use App\Models\Company;
use App\Models\Notification;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\ExpoPushService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
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

        $job->handle(app(ExpoPushService::class));

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

        $job->handle(app(ExpoPushService::class));

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

        $job->handle(app(ExpoPushService::class));

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

        $job->handle(app(ExpoPushService::class));

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

        $job->handle(app(ExpoPushService::class));

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

        $job->handle(app(ExpoPushService::class));

        $notification = Notification::where('user_id', $user->id)->first();

        $this->assertNotNull($notification);
        $this->assertEquals($project->id, $notification->data['project_id']);
        $this->assertEquals($project->name, $notification->data['project_name']);
        $this->assertEquals($task->due_at->toDateString(), $notification->data['due_at']);
    }

    public function test_job_sends_email_when_user_has_email_notifications_enabled(): void
    {
        Mail::fake();

        $user = User::factory()->create(['email_notifications_enabled' => true]);
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

        $job->handle(app(ExpoPushService::class));

        Mail::assertQueued(AlertMailable::class, function ($mail) use ($user, $overdueTask) {
            return $mail->user->id === $user->id
                && $mail->alertType === 'overdue'
                && $mail->overdueTasks->contains($overdueTask);
        });
    }

    public function test_job_sends_email_for_near_due_tasks(): void
    {
        Mail::fake();

        $user = User::factory()->create(['email_notifications_enabled' => true]);
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

        $job->handle(app(ExpoPushService::class));

        Mail::assertQueued(AlertMailable::class, function ($mail) use ($user, $nearDueTask) {
            return $mail->user->id === $user->id
                && $mail->alertType === 'near_due'
                && $mail->nearDueTasks->contains($nearDueTask);
        });
    }

    public function test_job_does_not_send_email_when_user_has_email_notifications_disabled(): void
    {
        Mail::fake();

        $user = User::factory()->create(['email_notifications_enabled' => false]);
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

        $job->handle(app(ExpoPushService::class));

        Mail::assertNothingQueued();
    }

    public function test_job_sends_separate_emails_for_overdue_and_near_due_tasks(): void
    {
        Mail::fake();

        $user = User::factory()->create(['email_notifications_enabled' => true]);
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

        $nearDueTask = Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'assignee_id' => $user->id,
            'due_at' => Carbon::now()->addDays(2),
            'status' => TaskStatus::in_progress,
        ]);

        $job = new SendAlertJob(
            $user->id,
            collect([$overdueTask]),
            collect([$nearDueTask]),
            []
        );

        $job->handle(app(ExpoPushService::class));

        Mail::assertQueuedCount(2);
        Mail::assertQueued(AlertMailable::class, function ($mail) {
            return $mail->alertType === 'overdue';
        });
        Mail::assertQueued(AlertMailable::class, function ($mail) {
            return $mail->alertType === 'near_due';
        });
    }

    public function test_job_sends_push_notification_when_user_has_expo_token(): void
    {
        Http::fake([
            'exp.host/--/api/v2/push/send' => Http::response([
                'data' => [
                    [
                        'status' => 'ok',
                        'id' => 'test-id',
                    ],
                ],
            ], 200),
        ]);

        $user = User::factory()->create([
            'expo_push_token' => 'ExponentPushToken[test-token]',
        ]);
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

        $job->handle(app(ExpoPushService::class));

        Http::assertSent(function ($request) {
            $payload = $request->data()[0];

            return $request->url() === 'https://exp.host/--/api/v2/push/send'
                && $payload['to'] === 'ExponentPushToken[test-token]'
                && isset($payload['title'])
                && isset($payload['body'])
                && isset($payload['data'])
                && isset($payload['sound'])
                && isset($payload['badge']);
        });
    }

    public function test_job_does_not_send_push_when_user_has_no_expo_token(): void
    {
        Http::fake();

        $user = User::factory()->create([
            'expo_push_token' => null,
        ]);
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

        $job->handle(app(ExpoPushService::class));

        Http::assertNothingSent();
    }

    public function test_job_includes_expo_channel_in_notification_channels(): void
    {
        $user = User::factory()->create([
            'expo_push_token' => 'ExponentPushToken[test-token]',
        ]);
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

        $job->handle(app(ExpoPushService::class));

        $notification = Notification::where('user_id', $user->id)->first();

        $this->assertNotNull($notification);
        $this->assertContains('expo', $notification->channels);
        $this->assertContains('database', $notification->channels);
    }

    public function test_job_sends_push_with_correct_title_and_body_for_single_task(): void
    {
        Http::fake([
            'exp.host/--/api/v2/push/send' => Http::response([
                'data' => [['status' => 'ok']],
            ], 200),
        ]);

        $user = User::factory()->create([
            'expo_push_token' => 'ExponentPushToken[test-token]',
        ]);
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        $project = Project::factory()->create([
            'company_id' => $company->id,
            'name' => 'Test Project',
        ]);

        $overdueTask = Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'assignee_id' => $user->id,
            'title' => 'Test Task',
            'due_at' => Carbon::now()->subDays(5),
            'status' => TaskStatus::in_progress,
        ]);

        $job = new SendAlertJob(
            $user->id,
            collect([$overdueTask]),
            collect([]),
            []
        );

        $job->handle(app(ExpoPushService::class));

        Http::assertSent(function ($request) use ($overdueTask, $project) {
            $payload = $request->data()[0];

            return $payload['title'] === 'Tarefa Atrasada'
                && str_contains($payload['body'], $overdueTask->title)
                && str_contains($payload['body'], $project->name);
        });
    }

    public function test_job_sends_push_with_correct_title_for_multiple_tasks(): void
    {
        Http::fake([
            'exp.host/--/api/v2/push/send' => Http::response([
                'data' => [['status' => 'ok']],
            ], 200),
        ]);

        $user = User::factory()->create([
            'expo_push_token' => 'ExponentPushToken[test-token]',
        ]);
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

        $job = new SendAlertJob(
            $user->id,
            collect([$overdueTask1, $overdueTask2]),
            collect([]),
            []
        );

        $job->handle(app(ExpoPushService::class));

        Http::assertSent(function ($request) {
            $payload = $request->data()[0];

            return $payload['title'] === '2 Tarefas Atrasadas'
                && str_contains($payload['body'], '2 novas notificações');
        });
    }
}

