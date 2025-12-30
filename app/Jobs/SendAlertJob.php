<?php

namespace App\Jobs;

use App\Mail\AlertMailable;
use App\Models\Notification;
use App\Models\Task;
use App\Models\User;
use App\Services\ExpoPushService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendAlertJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     *
     * @param  int  $userId
     * @param  Collection<int, Task>  $overdueTasks
     * @param  Collection<int, Task>  $nearDueTasks
     * @param  array<int, mixed>  $expiringLicenses
     */
    public function __construct(
        public int $userId,
        public Collection $overdueTasks,
        public Collection $nearDueTasks,
        public array $expiringLicenses = []
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(ExpoPushService $expoPushService): void
    {
        $user = User::find($this->userId);

        if (! $user) {
            return;
        }

        $notificationCount = 0;
        $firstNotification = null;
        
        // Determine channels based on user's expo token
        $channels = ['database'];
        if ($user->expo_push_token) {
            $channels[] = 'expo';
        }

        // Create notifications for overdue tasks
        foreach ($this->overdueTasks as $task) {
            $notification = Notification::create([
                'user_id' => $this->userId,
                'notifiable_id' => $task->id,
                'notifiable_type' => Task::class,
                'type' => 'task.overdue',
                'data' => [
                    'task_id' => $task->id,
                    'task_title' => $task->title,
                    'due_at' => $task->due_at?->toDateString(),
                    'project_id' => $task->project_id,
                    'project_name' => $task->project?->name,
                ],
                'channels' => $channels,
            ]);

            if ($notificationCount === 0) {
                $firstNotification = $notification;
            }
            $notificationCount++;
        }

        // Create notifications for near-due tasks
        foreach ($this->nearDueTasks as $task) {
            $notification = Notification::create([
                'user_id' => $this->userId,
                'notifiable_id' => $task->id,
                'notifiable_type' => Task::class,
                'type' => 'task.near_due',
                'data' => [
                    'task_id' => $task->id,
                    'task_title' => $task->title,
                    'due_at' => $task->due_at?->toDateString(),
                    'project_id' => $task->project_id,
                    'project_name' => $task->project?->name,
                ],
                'channels' => $channels,
            ]);

            if ($notificationCount === 0) {
                $firstNotification = $notification;
            }
            $notificationCount++;
        }

        // Send push notification if user has Expo token
        if ($user->expo_push_token && $notificationCount > 0 && $firstNotification) {
            try {
                $title = $this->getNotificationTitle($firstNotification->type, $notificationCount);
                $body = $this->getNotificationBody($firstNotification, $notificationCount);

                $expoPushService->sendPush(
                    $user->expo_push_token,
                    $title,
                    $body,
                    [
                        'notification_id' => $firstNotification->id,
                        'type' => $firstNotification->type,
                        'data' => $firstNotification->data,
                    ],
                    [
                        'sound' => 'default',
                        'badge' => $user->unreadUserNotifications()->count(),
                    ]
                );
            } catch (\Exception $e) {
                Log::error('Failed to send Expo push notification', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Send email alerts if user has email notifications enabled
        if ($user->email_notifications_enabled ?? true) {
            // Send email for overdue tasks
            if ($this->overdueTasks->isNotEmpty()) {
                Mail::to($user)->queue(new AlertMailable(
                    $user,
                    $this->overdueTasks,
                    collect(),
                    'overdue'
                ));
            }

            // Send email for near-due tasks
            if ($this->nearDueTasks->isNotEmpty()) {
                Mail::to($user)->queue(new AlertMailable(
                    $user,
                    collect(),
                    $this->nearDueTasks,
                    'near_due'
                ));
            }
        }

        // TODO: Create notifications for expiring licenses when License model is available
    }

    /**
     * Get notification title based on type and count
     */
    private function getNotificationTitle(string $type, int $count): string
    {
        if ($count === 1) {
            return match ($type) {
                'task.overdue' => 'Tarefa Atrasada',
                'task.near_due' => 'Tarefa Próxima do Vencimento',
                default => 'Nova Notificação',
            };
        }

        return match ($type) {
            'task.overdue' => "{$count} Tarefas Atrasadas",
            'task.near_due' => "{$count} Tarefas Próximas do Vencimento",
            default => "{$count} Novas Notificações",
        };
    }

    /**
     * Get notification body based on notification and count
     */
    private function getNotificationBody(Notification $notification, int $count): string
    {
        if ($count === 1) {
            $taskTitle = $notification->data['task_title'] ?? 'Tarefa';
            $projectName = $notification->data['project_name'] ?? '';

            if ($projectName) {
                return "{$taskTitle} - {$projectName}";
            }

            return $taskTitle;
        }

        return "Você tem {$count} novas notificações";
    }
}
