<?php

namespace App\Observers;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\TaskDependency;
use Illuminate\Validation\ValidationException;

class TaskObserver
{
    /**
     * Allowed tolerance in days for task start date before predecessor finish date.
     * Zero means task must start after or on the predecessor's finish date.
     */
    private const ALLOWED_DATE_TOLERANCE_DAYS = 0;

    /**
     * Handle the Task "saving" event.
     * Validate that task dates are consistent with its dependencies.
     */
    public function saving(Task $task): void
    {
        // Only validate if task has dates and project_id
        if (! $task->planned_start_at || ! $task->project_id) {
            return;
        }

        // Get all tasks that this task depends on (predecessors)
        $predecessorIds = TaskDependency::where('task_id', $task->id)
            ->pluck('depends_on_task_id')
            ->toArray();

        if (empty($predecessorIds)) {
            return; // No dependencies to validate
        }

        // Load predecessor tasks with their dates
        $predecessors = Task::whereIn('id', $predecessorIds)
            ->where('project_id', $task->project_id)
            ->get(['id', 'title', 'planned_start_at', 'planned_end_at']);

        $violations = [];

        foreach ($predecessors as $predecessor) {
            // Skip if predecessor doesn't have an end date
            if (! $predecessor->planned_end_at) {
                continue;
            }

            // Calculate the minimum allowed start date for the current task
            $minAllowedStartDate = $predecessor->planned_end_at->copy()->addDays(self::ALLOWED_DATE_TOLERANCE_DAYS);

            // Check if task's start date is before the minimum allowed date
            if ($task->planned_start_at->lt($minAllowedStartDate)) {
                $violations[] = [
                    'task_id' => $predecessor->id,
                    'task_title' => $predecessor->title,
                    'predecessor_finish_date' => $predecessor->planned_end_at->format('Y-m-d'),
                    'task_start_date' => $task->planned_start_at->format('Y-m-d'),
                    'min_allowed_start_date' => $minAllowedStartDate->format('Y-m-d'),
                ];
            }
        }

        if (! empty($violations)) {
            $messages = collect($violations)->map(function ($violation) {
                return sprintf(
                    'Task "%s" (ID: %d) must finish before this task can start. '.
                    'Predecessor finishes on %s, but this task starts on %s. '.
                    'Minimum allowed start date is %s.',
                    $violation['task_title'],
                    $violation['task_id'],
                    $violation['predecessor_finish_date'],
                    $violation['task_start_date'],
                    $violation['min_allowed_start_date']
                );
            })->toArray();

            throw ValidationException::withMessages([
                'planned_start_at' => $messages,
            ]);
        }
    }

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
