<?php

namespace App\Jobs;

use App\Mail\AlertMailable;
use App\Models\Notification;
use App\Models\Task;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
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
    public function handle(): void
    {
        $user = User::find($this->userId);

        if (! $user) {
            return;
        }

        // Create notifications for overdue tasks
        foreach ($this->overdueTasks as $task) {
            Notification::create([
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
                'channels' => ['database'],
            ]);
        }

        // Create notifications for near-due tasks
        foreach ($this->nearDueTasks as $task) {
            Notification::create([
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
                'channels' => ['database'],
            ]);
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
}
