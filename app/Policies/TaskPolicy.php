<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Determine whether the user can view any tasks.
     */
    public function viewAny(User $user, Project $project): bool
    {
        // User must belong to the company and be a member of the project
        return $user->companies()->whereKey($project->company_id)->exists()
            && $user->projects()->whereKey($project->id)->exists();
    }

    /**
     * Determine whether the user can view the task.
     */
    public function view(User $user, Task $task): bool
    {
        // User must be a member of the project
        return $user->projects()->whereKey($task->project_id)->exists();
    }

    /**
     * Determine whether the user can create tasks.
     */
    public function create(User $user, Project $project): bool
    {
        // User must be a member of the project
        return $user->projects()->whereKey($project->id)->exists();
    }

    /**
     * Determine whether the user can update the task.
     */
    public function update(User $user, Task $task): bool
    {
        // User must be a member of the project OR be the assignee
        return $user->projects()->whereKey($task->project_id)->exists()
            || $task->assignee_id === $user->id;
    }

    /**
     * Determine whether the user can delete the task.
     */
    public function delete(User $user, Task $task): bool
    {
        // User must be a member of the project
        return $user->projects()->whereKey($task->project_id)->exists();
    }
}
