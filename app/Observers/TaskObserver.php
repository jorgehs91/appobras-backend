<?php

namespace App\Observers;

use App\Enums\TaskStatus;
use App\Models\Task;

class TaskObserver
{
    /**
     * Handle the Task "updating" event.
     * Automatically manage started_at and completed_at timestamps based on status changes.
     */
    public function updating(Task $task): void
    {
        // Check if status is being changed
        if ($task->isDirty('status')) {
            $newStatus = $task->status;
            $oldStatus = $task->getOriginal('status');

            // When task moves to in_progress, set started_at if not already set
            if ($newStatus === TaskStatus::in_progress && is_null($task->started_at)) {
                $task->started_at = now();
            }

            // When task moves to done, set completed_at
            if ($newStatus === TaskStatus::done) {
                $task->completed_at = now();
            }

            // When task moves away from done to another status, clear completed_at
            if ($oldStatus === TaskStatus::done->value && $newStatus !== TaskStatus::done) {
                $task->completed_at = null;
            }
        }
    }
}
