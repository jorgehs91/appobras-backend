<?php

namespace App\Http\Controllers;

use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Requests\Task\UpdateTaskStatusRequest;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Tasks",
 *     description="Gerenciamento de tarefas do projeto"
 * )
 */
class TaskController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/projects/{project}/tasks",
     *     summary="Listar tarefas do projeto",
     *     description="Retorna todas as tarefas de um projeto com filtros opcionais",
     *     tags={"Tasks"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="project", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="phase_id", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="status", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="assignee_id", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Lista de tarefas")
     * )
     */
    public function index(Request $request, Project $project): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($project->company_id === $companyId, 403);
        abort_unless($user->projects()->whereKey($project->id)->exists(), 403);

        $query = Task::query()
            ->where('project_id', $project->id)
            ->with(['phase', 'assignee', 'contractor'])
            ->orderBy('order_in_phase');

        // Filter by phase_id if provided
        if ($request->has('phase_id')) {
            $query->where('phase_id', $request->input('phase_id'));
        }

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by assignee_id if provided
        if ($request->has('assignee_id')) {
            $query->where('assignee_id', $request->input('assignee_id'));
        }

        $tasks = $query->get();

        return TaskResource::collection($tasks)->response();
    }

    /**
     * Store a newly created task.
     */
    public function store(StoreTaskRequest $request, Project $project): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($project->company_id === $companyId, 403);
        abort_unless($user->projects()->whereKey($project->id)->exists(), 403);

        $payload = $request->validated();
        $payload['company_id'] = $companyId;
        $payload['project_id'] = $project->id;

        $task = Task::query()->create($payload);

        return (new TaskResource($task->load(['phase', 'assignee', 'contractor'])))->response()->setStatusCode(201);
    }

    /**
     * Update the specified task.
     */
    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($task->company_id === $companyId, 403);
        abort_unless($user->projects()->whereKey($task->project_id)->exists() || $task->assignee_id === $user->id, 403);

        $task->fill($request->validated());
        $task->save();

        return (new TaskResource($task->load(['phase', 'assignee', 'contractor'])))->response();
    }

    /**
     * Update only the status of the task (shortcut for Kanban drag-and-drop).
     */
    public function updateStatus(UpdateTaskStatusRequest $request, Task $task): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($task->company_id === $companyId, 403);
        abort_unless($user->projects()->whereKey($task->project_id)->exists() || $task->assignee_id === $user->id, 403);

        $task->status = $request->validated()['status'];
        $task->save();

        return (new TaskResource($task->load(['phase', 'assignee', 'contractor'])))->response();
    }

    /**
     * Remove the specified task (soft delete).
     */
    public function destroy(Request $request, Task $task): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($task->company_id === $companyId, 403);
        abort_unless($user->projects()->whereKey($task->project_id)->exists(), 403);

        $task->delete();

        return response()->json(null, 204);
    }
}

