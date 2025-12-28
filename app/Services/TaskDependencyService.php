<?php

namespace App\Services;

use App\Models\Task;
use App\Models\TaskDependency;

class TaskDependencyService
{
    /**
     * Maximum recursion depth to prevent infinite loops in very large graphs.
     */
    private const MAX_DEPTH = 1000;

    /**
     * Check if a dependency can be added without creating a cycle.
     *
     * @param  int  $taskId  The task that will depend on another task
     * @param  int  $dependsOnTaskId  The task that will be depended upon
     * @return bool  True if the dependency can be added, false if it would create a cycle
     */
    public function canAddDependency(int $taskId, int $dependsOnTaskId): bool
    {
        return $this->detectCycleOnAdd($taskId, $dependsOnTaskId) === null;
    }

    /**
     * Detect if adding a dependency would create a cycle.
     *
     * @param  int  $taskId  The task that will depend on another task
     * @param  int  $dependsOnTaskId  The task that will be depended upon
     * @return array<int>|null  The cycle path if a cycle is detected, null otherwise
     */
    public function detectCycleOnAdd(int $taskId, int $dependsOnTaskId): ?array
    {
        // Self-loop check: task cannot depend on itself
        if ($taskId === $dependsOnTaskId) {
            return [$taskId, $dependsOnTaskId];
        }

        // Verify both tasks exist and belong to the same project
        $task = Task::find($taskId);
        $dependsOnTask = Task::find($dependsOnTaskId);

        if (! $task || ! $dependsOnTask) {
            return null; // One of the tasks doesn't exist, let validation handle this
        }

        // Cross-project dependency check: reject if tasks belong to different projects
        if ($task->project_id !== $dependsOnTask->project_id) {
            return null; // Cross-project dependency, let validation handle this
        }

        // Check if dependsOnTaskId (or any of its dependencies) already depends on taskId
        // This would create a cycle: taskId -> dependsOnTaskId -> ... -> taskId
        $cycle = $this->findPath($dependsOnTaskId, $taskId, $task->project_id);

        if ($cycle !== null) {
            // Add taskId at the beginning to complete the cycle path
            array_unshift($cycle, $taskId);
            return $cycle;
        }

        return null;
    }

    /**
     * Find a path from startTaskId to targetTaskId using DFS.
     *
     * @param  int  $startTaskId  Starting task ID
     * @param  int  $targetTaskId  Target task ID to find
     * @param  int  $projectId  Project ID to scope the search
     * @return array<int>|null  Path from start to target, or null if no path exists
     */
    private function findPath(int $startTaskId, int $targetTaskId, int $projectId): ?array
    {
        // Use iterative DFS to avoid stack overflow on large graphs
        // Use a set to track visited nodes globally to avoid revisiting in different paths
        // This prevents infinite loops while still allowing exploration
        $visited = [];
        $stack = [[$startTaskId, [$startTaskId]]]; // [currentNode, path]

        while (! empty($stack)) {
            [$currentTaskId, $path] = array_pop($stack);

            // Prevent infinite loops by checking depth
            if (count($path) > self::MAX_DEPTH) {
                continue;
            }

            // If we've reached the target, return the path
            if ($currentTaskId === $targetTaskId) {
                return $path;
            }

            // Mark as visited to avoid revisiting in different paths
            // This prevents exponential explosion while still finding valid paths
            if (isset($visited[$currentTaskId])) {
                continue;
            }

            $visited[$currentTaskId] = true;

            // Get all tasks that currentTaskId depends on (outgoing edges in dependency graph)
            $dependencies = $this->getDependencies($currentTaskId, $projectId);

            foreach ($dependencies as $dependentTaskId) {
                // Skip if already in current path (back edge in current path)
                if (in_array($dependentTaskId, $path, true)) {
                    continue;
                }

                $newPath = $path;
                $newPath[] = $dependentTaskId;
                $stack[] = [$dependentTaskId, $newPath];
            }
        }

        return null; // No path found
    }

    /**
     * Get all task IDs that the given task depends on (within the same project).
     * Only returns active (non-soft-deleted) dependencies.
     *
     * @param  int  $taskId  Task ID
     * @param  int  $projectId  Project ID to scope the query
     * @return array<int>  Array of task IDs that taskId depends on
     */
    private function getDependencies(int $taskId, int $projectId): array
    {
        return TaskDependency::where('task_id', $taskId)
            ->whereHas('dependsOnTask', function ($query) use ($projectId) {
                $query->where('project_id', $projectId);
            })
            ->pluck('depends_on_task_id')
            ->toArray();
    }
}

