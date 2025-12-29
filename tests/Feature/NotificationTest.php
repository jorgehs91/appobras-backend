<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Notification;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_notification(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $user->companies()->attach($company->id);
        $project = Project::factory()->create(['company_id' => $company->id]);

        $notification = Notification::factory()->create([
            'user_id' => $user->id,
            'notifiable_id' => $project->id,
            'notifiable_type' => Project::class,
            'type' => 'project_updated',
        ]);

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'user_id' => $user->id,
            'notifiable_id' => $project->id,
            'notifiable_type' => Project::class,
            'type' => 'project_updated',
        ]);
    }

    public function test_notification_has_polymorphic_relationship(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $user->companies()->attach($company->id);
        $project = Project::factory()->create(['company_id' => $company->id]);

        $notification = Notification::factory()->create([
            'user_id' => $user->id,
            'notifiable_id' => $project->id,
            'notifiable_type' => Project::class,
        ]);

        $this->assertInstanceOf(Project::class, $notification->notifiable);
        $this->assertEquals($project->id, $notification->notifiable->id);
    }

    public function test_notification_can_be_marked_as_read(): void
    {
        $user = User::factory()->create();
        $notification = Notification::factory()->unread()->create([
            'user_id' => $user->id,
        ]);

        $this->assertNull($notification->read_at);
        $this->assertTrue($notification->isUnread());

        $result = $notification->markAsRead();

        $this->assertTrue($result);
        $this->assertNotNull($notification->fresh()->read_at);
        $this->assertTrue($notification->fresh()->isRead());
    }

    public function test_notification_can_be_marked_as_unread(): void
    {
        $user = User::factory()->create();
        $notification = Notification::factory()->read()->create([
            'user_id' => $user->id,
        ]);

        $this->assertNotNull($notification->read_at);
        $this->assertTrue($notification->isRead());

        $result = $notification->markAsUnread();

        $this->assertTrue($result);
        $this->assertNull($notification->fresh()->read_at);
        $this->assertTrue($notification->fresh()->isUnread());
    }

    public function test_user_can_access_notifications(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $user->companies()->attach($company->id);

        $notification1 = Notification::factory()->create(['user_id' => $user->id]);
        $notification2 = Notification::factory()->create(['user_id' => $user->id]);
        $otherUser = User::factory()->create();
        $notification3 = Notification::factory()->create(['user_id' => $otherUser->id]);

        $userNotifications = $user->userNotifications;

        $this->assertCount(2, $userNotifications);
        $this->assertTrue($userNotifications->contains($notification1));
        $this->assertTrue($userNotifications->contains($notification2));
        $this->assertFalse($userNotifications->contains($notification3));
    }

    public function test_user_can_access_unread_notifications(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $user->companies()->attach($company->id);

        $unread1 = Notification::factory()->unread()->create(['user_id' => $user->id]);
        $unread2 = Notification::factory()->unread()->create(['user_id' => $user->id]);
        $read = Notification::factory()->read()->create(['user_id' => $user->id]);

        $unreadNotifications = $user->unreadUserNotifications;

        $this->assertCount(2, $unreadNotifications);
        $this->assertTrue($unreadNotifications->contains($unread1));
        $this->assertTrue($unreadNotifications->contains($unread2));
        $this->assertFalse($unreadNotifications->contains($read));
    }

    public function test_user_can_access_read_notifications(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $user->companies()->attach($company->id);

        $read1 = Notification::factory()->read()->create(['user_id' => $user->id]);
        $read2 = Notification::factory()->read()->create(['user_id' => $user->id]);
        $unread = Notification::factory()->unread()->create(['user_id' => $user->id]);

        $readNotifications = $user->readUserNotifications;

        $this->assertCount(2, $readNotifications);
        $this->assertTrue($readNotifications->contains($read1));
        $this->assertTrue($readNotifications->contains($read2));
        $this->assertFalse($readNotifications->contains($unread));
    }

    public function test_notification_data_is_casted_to_array(): void
    {
        $user = User::factory()->create();
        $data = [
            'title' => 'Test Title',
            'message' => 'Test Message',
            'action_url' => 'https://example.com',
        ];

        $notification = Notification::factory()->create([
            'user_id' => $user->id,
            'data' => $data,
        ]);

        $this->assertIsArray($notification->data);
        $this->assertEquals($data, $notification->data);
    }

    public function test_notification_channels_is_casted_to_array(): void
    {
        $user = User::factory()->create();
        $channels = ['email', 'push'];

        $notification = Notification::factory()->create([
            'user_id' => $user->id,
            'channels' => $channels,
        ]);

        $this->assertIsArray($notification->channels);
        $this->assertEquals($channels, $notification->channels);
    }

    public function test_unread_scope_filters_unread_notifications(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $user->companies()->attach($company->id);

        Notification::factory()->unread()->count(3)->create(['user_id' => $user->id]);
        Notification::factory()->read()->count(2)->create(['user_id' => $user->id]);

        $unreadCount = Notification::query()->forUser($user->id)->unread()->count();

        $this->assertEquals(3, $unreadCount);
    }

    public function test_read_scope_filters_read_notifications(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $user->companies()->attach($company->id);

        Notification::factory()->unread()->count(2)->create(['user_id' => $user->id]);
        Notification::factory()->read()->count(4)->create(['user_id' => $user->id]);

        $readCount = Notification::query()->forUser($user->id)->read()->count();

        $this->assertEquals(4, $readCount);
    }

    public function test_by_type_scope_filters_by_notification_type(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $user->companies()->attach($company->id);

        Notification::factory()->count(3)->create([
            'user_id' => $user->id,
            'type' => 'task_assigned',
        ]);
        Notification::factory()->count(2)->create([
            'user_id' => $user->id,
            'type' => 'task_overdue',
        ]);

        $taskAssignedCount = Notification::query()->forUser($user->id)->byType('task_assigned')->count();

        $this->assertEquals(3, $taskAssignedCount);
    }

    public function test_notification_can_be_created_for_task(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $user->companies()->attach($company->id);
        $project = Project::factory()->create(['company_id' => $company->id]);
        $task = Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
        ]);

        $notification = Notification::factory()->create([
            'user_id' => $user->id,
            'notifiable_id' => $task->id,
            'notifiable_type' => Task::class,
            'type' => 'task_assigned',
        ]);

        $this->assertInstanceOf(Task::class, $notification->notifiable);
        $this->assertEquals($task->id, $notification->notifiable->id);
    }

    public function test_notification_can_be_created_for_project(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $user->companies()->attach($company->id);
        $project = Project::factory()->create(['company_id' => $company->id]);

        $notification = Notification::factory()->create([
            'user_id' => $user->id,
            'notifiable_id' => $project->id,
            'notifiable_type' => Project::class,
            'type' => 'project_updated',
        ]);

        $this->assertInstanceOf(Project::class, $notification->notifiable);
        $this->assertEquals($project->id, $notification->notifiable->id);
    }

    public function test_mark_as_read_returns_false_if_already_read(): void
    {
        $user = User::factory()->create();
        $notification = Notification::factory()->read()->create([
            'user_id' => $user->id,
        ]);

        $result = $notification->markAsRead();

        $this->assertFalse($result);
    }

    public function test_mark_as_unread_returns_false_if_already_unread(): void
    {
        $user = User::factory()->create();
        $notification = Notification::factory()->unread()->create([
            'user_id' => $user->id,
        ]);

        $result = $notification->markAsUnread();

        $this->assertFalse($result);
    }
}

