<?php

namespace App\Http\Controllers;

use App\Http\Requests\Task\BulkUpdateTaskRequest;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Requests\Task\UpdateTaskStatusRequest;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Task;
use App\Services\TaskBulkService;
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
    public function __construct(
        protected TaskBulkService $bulkService
    ) {
    }
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
     * @OA\Post(
     *     path="/api/v1/projects/{project}/tasks",
     *     summary="Criar tarefa",
     *     description="Cria uma nova tarefa no projeto",
     *     tags={"Tasks"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="project", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"phase_id", "title"},
     *             @OA\Property(property="phase_id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="Instalar estrutura metálica"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="status", type="string", enum={"backlog", "in_progress", "in_review", "done", "canceled"}),
     *             @OA\Property(property="priority", type="string", enum={"low", "medium", "high", "urgent"}),
     *             @OA\Property(property="order_in_phase", type="integer", example=1),
     *             @OA\Property(property="assignee_id", type="integer"),
     *             @OA\Property(property="contractor_id", type="integer"),
     *             @OA\Property(property="is_blocked", type="boolean"),
     *             @OA\Property(property="blocked_reason", type="string"),
     *             @OA\Property(property="planned_start_at", type="string", format="date"),
     *             @OA\Property(property="planned_end_at", type="string", format="date"),
     *             @OA\Property(property="due_at", type="string", format="date")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Tarefa criada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=422, description="Erro de validação")
     * )
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
     * @OA\Put(
     *     path="/api/v1/tasks/{task}",
     *     summary="Atualizar tarefa",
     *     description="Atualiza informações de uma tarefa existente",
     *     tags={"Tasks"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="task", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="phase_id", type="integer"),
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="status", type="string", enum={"backlog", "in_progress", "in_review", "done", "canceled"}),
     *             @OA\Property(property="priority", type="string", enum={"low", "medium", "high", "urgent"}),
     *             @OA\Property(property="order_in_phase", type="integer"),
     *             @OA\Property(property="assignee_id", type="integer"),
     *             @OA\Property(property="contractor_id", type="integer"),
     *             @OA\Property(property="is_blocked", type="boolean"),
     *             @OA\Property(property="blocked_reason", type="string"),
     *             @OA\Property(property="planned_start_at", type="string", format="date"),
     *             @OA\Property(property="planned_end_at", type="string", format="date"),
     *             @OA\Property(property="due_at", type="string", format="date")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tarefa atualizada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Tarefa não encontrada"),
     *     @OA\Response(response=422, description="Erro de validação")
     * )
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
     * @OA\Patch(
     *     path="/api/v1/projects/{project}/tasks/bulk",
     *     summary="Atualizar múltiplas tarefas",
     *     description="Atualiza múltiplas tarefas em uma única operação atômica. Permite atualizar posição (reorder), status e outros campos. Usa transações para garantir atomicidade.",
     *     tags={"Tasks"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="project", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"tasks"},
     *             @OA\Property(
     *                 property="tasks",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"id"},
     *                     @OA\Property(property="id", type="integer", example=1, description="ID da tarefa a ser atualizada"),
     *                     @OA\Property(property="position", type="number", format="float", example=1.1, description="Nova posição da tarefa (para reordenação no Kanban). Permite valores decimais como 1.1, 1.2, etc."),
     *                     @OA\Property(property="status", type="string", enum={"backlog", "in_progress", "in_review", "done", "canceled"}, example="in_progress"),
     *                     @OA\Property(property="phase_id", type="integer", example=2),
     *                     @OA\Property(property="priority", type="string", enum={"low", "medium", "high", "urgent"}),
     *                     @OA\Property(property="assignee_id", type="integer", example=5),
     *                     @OA\Property(property="contractor_id", type="integer", example=3),
     *                     @OA\Property(property="is_blocked", type="boolean", example=false),
     *                     @OA\Property(property="blocked_reason", type="string", example="Aguardando material")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tarefas atualizadas com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=422, description="Erro de validação (tarefa não encontrada, não pertence ao projeto, etc)")
     * )
     */
    public function bulkUpdate(BulkUpdateTaskRequest $request, Project $project): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($project->company_id === $companyId, 403);
        abort_unless($user->projects()->whereKey($project->id)->exists(), 403);

        $validated = $request->validated();
        $tasksData = $validated['tasks'];

        $updatedTasks = $this->bulkService->bulkUpdate($tasksData, $project);

        return TaskResource::collection($updatedTasks)->response();
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/tasks/{task}/status",
     *     summary="Atualizar status da tarefa",
     *     description="Atualiza apenas o status da tarefa (atalho para drag-and-drop no Kanban)",
     *     tags={"Tasks"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="task", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"backlog", "in_progress", "in_review", "done", "canceled"}, example="in_progress")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Status atualizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Tarefa não encontrada"),
     *     @OA\Response(response=422, description="Erro de validação")
     * )
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
     * @OA\Delete(
     *     path="/api/v1/tasks/{task}",
     *     summary="Remover tarefa",
     *     description="Remove uma tarefa (soft delete)",
     *     tags={"Tasks"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="task", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=204,
     *         description="Tarefa removida com sucesso"
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Tarefa não encontrada")
     * )
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

