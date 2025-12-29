<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\Task;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;

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

        // TODO: Create notifications for expiring licenses when License model is available
    }
}
