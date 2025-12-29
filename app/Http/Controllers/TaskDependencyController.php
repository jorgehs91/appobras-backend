<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskDependency\StoreTaskDependencyRequest;
use App\Http\Requests\TaskDependency\UpdateTaskDependencyRequest;
use App\Models\Project;
use App\Models\TaskDependency;
use App\Services\TaskDependencyService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(
 *     name="Task Dependencies",
 *     description="Gerenciamento de dependências entre tarefas"
 * )
 */
class TaskDependencyController extends Controller
{
    public function __construct(
        protected TaskDependencyService $dependencyService
    ) {
    }

    /**
     * @OA\Get(
     *     path="/api/v1/task-dependencies",
     *     summary="Listar dependências de tarefas",
     *     description="Lista dependências de tarefas filtradas por task_id, com escopo por project_id. Retorna todas as dependências onde a tarefa especificada é dependente ou é requisito.",
     *     tags={"Task Dependencies"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="task_id", in="query", required=true, @OA\Schema(type="integer"), description="ID da tarefa para filtrar dependências"),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de dependências",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="task_id", type="integer", example=1),
     *                 @OA\Property(property="depends_on_task_id", type="integer", example=2),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             ))
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=422, description="Erro de validação (task_id obrigatório)")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);

        $request->validate([
            'task_id' => ['required', 'integer', 'exists:tasks,id'],
        ]);

        $taskId = (int) $request->input('task_id');

        // Verify task belongs to user's company and user has access to the project
        $task = \App\Models\Task::find($taskId);
        abort_unless($task, 404);
        abort_unless($task->company_id === $companyId, 403);
        abort_unless($user->projects()->whereKey($task->project_id)->exists(), 403);

        // Get dependencies where task_id is the dependent task or the prerequisite task
        $projectId = $task->project_id;
        $dependencies = TaskDependency::query()
            ->where(function ($query) use ($taskId) {
                $query->where('task_id', $taskId)
                    ->orWhere('depends_on_task_id', $taskId);
            })
            ->whereHas('task', function ($query) use ($projectId) {
                $query->where('project_id', $projectId);
            })
            ->whereHas('dependsOnTask', function ($query) use ($projectId) {
                $query->where('project_id', $projectId);
            })
            ->with(['task', 'dependsOnTask'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $dependencies->map(fn ($dep) => [
                'id' => $dep->id,
                'task_id' => $dep->task_id,
                'depends_on_task_id' => $dep->depends_on_task_id,
                'task' => [
                    'id' => $dep->task->id,
                    'title' => $dep->task->title,
                ],
                'depends_on_task' => [
                    'id' => $dep->dependsOnTask->id,
                    'title' => $dep->dependsOnTask->title,
                ],
                'created_at' => $dep->created_at,
                'updated_at' => $dep->updated_at,
            ]),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/projects/{project}/task-dependencies",
     *     summary="Criar dependência entre tarefas",
     *     description="Cria uma nova dependência entre duas tarefas do projeto. Previne ciclos e valida que as tarefas pertencem ao mesmo projeto.",
     *     tags={"Task Dependencies"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="project", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"task_id", "depends_on_task_id"},
     *             @OA\Property(property="task_id", type="integer", example=1, description="ID da tarefa que terá a dependência"),
     *             @OA\Property(property="depends_on_task_id", type="integer", example=2, description="ID da tarefa que é requisito (predecessora)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Dependência criada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="task_id", type="integer", example=1),
     *                 @OA\Property(property="depends_on_task_id", type="integer", example=2),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=422, description="Erro de validação (ciclo detectado, dependência duplicada, etc)")
     * )
     */
    public function store(StoreTaskDependencyRequest $request, Project $project): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($project->company_id === $companyId, 403);
        abort_unless($user->projects()->whereKey($project->id)->exists(), 403);

        $validated = $request->validated();
        $taskId = (int) $validated['task_id'];
        $dependsOnTaskId = (int) $validated['depends_on_task_id'];

        // Validate cycle detection
        $cycle = $this->dependencyService->detectCycleOnAdd($taskId, $dependsOnTaskId);

        if ($cycle !== null) {
            $cyclePath = implode(' -> ', $cycle);
            Log::warning('Cycle detection prevented dependency creation', [
                'task_id' => $taskId,
                'depends_on_task_id' => $dependsOnTaskId,
                'cycle_path' => $cycle,
            ]);

            throw ValidationException::withMessages([
                'depends_on_task_id' => [
                    sprintf(
                        'Esta dependência criaria um ciclo: %s. Uma tarefa não pode depender de outra que direta ou indiretamente depende dela.',
                        $cyclePath
                    ),
                ],
            ]);
        }

        // Check if dependency already exists
        $existing = TaskDependency::where('task_id', $taskId)
            ->where('depends_on_task_id', $dependsOnTaskId)
            ->exists();

        if ($existing) {
            throw ValidationException::withMessages([
                'depends_on_task_id' => ['Esta dependência já existe.'],
            ]);
        }

        // Create dependency (date validation will happen via TaskObserver when task dates are saved)
        try {
            $taskDependency = TaskDependency::create([
                'task_id' => $taskId,
                'depends_on_task_id' => $dependsOnTaskId,
            ]);
        } catch (QueryException $e) {
            // Handle unique constraint violation (duplicate dependency)
            if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'uk_task_dependencies_task_depends')) {
                Log::warning('Duplicate dependency creation attempted', [
                    'task_id' => $taskId,
                    'depends_on_task_id' => $dependsOnTaskId,
                ]);

                throw ValidationException::withMessages([
                    'depends_on_task_id' => ['Esta dependência já existe.'],
                ]);
            }

            throw $e;
        }

        return response()->json([
            'data' => [
                'id' => $taskDependency->id,
                'task_id' => $taskDependency->task_id,
                'depends_on_task_id' => $taskDependency->depends_on_task_id,
                'created_at' => $taskDependency->created_at,
                'updated_at' => $taskDependency->updated_at,
            ],
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/projects/{project}/task-dependencies/bulk",
     *     summary="Criar múltiplas dependências",
     *     description="Cria múltiplas dependências em uma única operação. Se qualquer dependência for inválida (ciclo, duplicada), toda a operação é revertida.",
     *     tags={"Task Dependencies"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="project", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"dependencies"},
     *             @OA\Property(property="dependencies", type="array", @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="task_id", type="integer", example=1),
     *                 @OA\Property(property="depends_on_task_id", type="integer", example=2)
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Dependências criadas com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=422, description="Erro de validação")
     * )
     */
    public function storeBulk(Request $request, Project $project): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($project->company_id === $companyId, 403);
        abort_unless($user->projects()->whereKey($project->id)->exists(), 403);

        $request->validate([
            'dependencies' => ['required', 'array', 'min:1'],
            'dependencies.*.task_id' => ['required', 'integer', 'exists:tasks,id'],
            'dependencies.*.depends_on_task_id' => ['required', 'integer', 'exists:tasks,id', 'different:dependencies.*.task_id'],
        ]);

        $dependencies = $request->input('dependencies');

        // Validate all dependencies before creating any (transaction)
        $errors = [];
        foreach ($dependencies as $index => $dependency) {
            $taskId = (int) $dependency['task_id'];
            $dependsOnTaskId = (int) $dependency['depends_on_task_id'];

            // Verify tasks belong to the project
            $task = \App\Models\Task::find($taskId);
            $dependsOnTask = \App\Models\Task::find($dependsOnTaskId);

            if (! $task || $task->project_id !== $project->id) {
                $errors["dependencies.{$index}.task_id"] = ['A tarefa não pertence ao projeto.'];
                continue;
            }

            if (! $dependsOnTask || $dependsOnTask->project_id !== $project->id) {
                $errors["dependencies.{$index}.depends_on_task_id"] = ['A tarefa dependente não pertence ao projeto.'];
                continue;
            }

            // Validate cycle detection
            $cycle = $this->dependencyService->detectCycleOnAdd($taskId, $dependsOnTaskId);
            if ($cycle !== null) {
                $cyclePath = implode(' -> ', $cycle);
                $errors["dependencies.{$index}.depends_on_task_id"] = [
                    sprintf(
                        'Esta dependência criaria um ciclo: %s.',
                        $cyclePath
                    ),
                ];
            }

            // Check for duplicate
            $exists = TaskDependency::where('task_id', $taskId)
                ->where('depends_on_task_id', $dependsOnTaskId)
                ->exists();

            if ($exists) {
                $errors["dependencies.{$index}"] = ['Esta dependência já existe.'];
            }
        }

        if (! empty($errors)) {
            Log::warning('Bulk dependency creation failed validation', [
                'project_id' => $project->id,
                'errors' => $errors,
            ]);

            throw ValidationException::withMessages($errors);
        }

        // Create all dependencies in a transaction
        try {
            DB::beginTransaction();

            $created = [];
            foreach ($dependencies as $index => $dependency) {
                try {
                    $taskDependency = TaskDependency::create([
                        'task_id' => $dependency['task_id'],
                        'depends_on_task_id' => $dependency['depends_on_task_id'],
                    ]);
                    $created[] = $taskDependency;
                } catch (QueryException $e) {
                    DB::rollBack();
                    // Handle unique constraint violation (duplicate dependency)
                    if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'uk_task_dependencies_task_depends')) {
                        Log::warning('Duplicate dependency in bulk creation', [
                            'index' => $index,
                            'task_id' => $dependency['task_id'],
                            'depends_on_task_id' => $dependency['depends_on_task_id'],
                        ]);

                        throw ValidationException::withMessages([
                            "dependencies.{$index}.depends_on_task_id" => ['Esta dependência já existe.'],
                        ]);
                    }

                    throw $e;
                }
            }

            DB::commit();

            return response()->json([
                'data' => collect($created)->map(fn ($dep) => [
                    'id' => $dep->id,
                    'task_id' => $dep->task_id,
                    'depends_on_task_id' => $dep->depends_on_task_id,
                    'created_at' => $dep->created_at,
                    'updated_at' => $dep->updated_at,
                ]),
            ], 201);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk dependency creation failed', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
            ]);

            throw ValidationException::withMessages([
                'dependencies' => ['Erro ao criar dependências. Tente novamente.'],
            ]);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/task-dependencies/{taskDependency}",
     *     summary="Atualizar dependência",
     *     description="Atualiza uma dependência existente. Valida ciclos se task_id ou depends_on_task_id forem alterados.",
     *     tags={"Task Dependencies"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="taskDependency", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="task_id", type="integer", example=1),
     *             @OA\Property(property="depends_on_task_id", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Dependência atualizada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="task_id", type="integer", example=1),
     *                 @OA\Property(property="depends_on_task_id", type="integer", example=2),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Dependência não encontrada"),
     *     @OA\Response(response=422, description="Erro de validação")
     * )
     */
    public function update(UpdateTaskDependencyRequest $request, TaskDependency $taskDependency): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($taskDependency->task->company_id === $companyId, 403);
        abort_unless($user->projects()->whereKey($taskDependency->task->project_id)->exists(), 403);

        $validated = $request->validated();

        // If updating task_id or depends_on_task_id, validate cycle detection
        if (isset($validated['task_id']) || isset($validated['depends_on_task_id'])) {
            $taskId = (int) ($validated['task_id'] ?? $taskDependency->task_id);
            $dependsOnTaskId = (int) ($validated['depends_on_task_id'] ?? $taskDependency->depends_on_task_id);

            $cycle = $this->dependencyService->detectCycleOnAdd($taskId, $dependsOnTaskId);

            if ($cycle !== null) {
                $cyclePath = implode(' -> ', $cycle);
                Log::warning('Cycle detection prevented dependency update', [
                    'task_dependency_id' => $taskDependency->id,
                    'task_id' => $taskId,
                    'depends_on_task_id' => $dependsOnTaskId,
                    'cycle_path' => $cycle,
                ]);

                throw ValidationException::withMessages([
                    'depends_on_task_id' => [
                        sprintf(
                            'Esta dependência criaria um ciclo: %s. Uma tarefa não pode depender de outra que direta ou indiretamente depende dela.',
                            $cyclePath
                        ),
                    ],
                ]);
            }
        }

        try {
            $taskDependency->update($validated);
        } catch (QueryException $e) {
            // Handle unique constraint violation (duplicate dependency)
            if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'uk_task_dependencies_task_depends')) {
                Log::warning('Duplicate dependency update attempted', [
                    'task_dependency_id' => $taskDependency->id,
                    'task_id' => $validated['task_id'] ?? $taskDependency->task_id,
                    'depends_on_task_id' => $validated['depends_on_task_id'] ?? $taskDependency->depends_on_task_id,
                ]);

                throw ValidationException::withMessages([
                    'depends_on_task_id' => ['Esta dependência já existe.'],
                ]);
            }

            throw $e;
        }

        return response()->json([
            'data' => [
                'id' => $taskDependency->id,
                'task_id' => $taskDependency->task_id,
                'depends_on_task_id' => $taskDependency->depends_on_task_id,
                'created_at' => $taskDependency->created_at,
                'updated_at' => $taskDependency->updated_at,
            ],
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/task-dependencies/{taskDependency}",
     *     summary="Remover dependência",
     *     description="Remove uma dependência entre tarefas (soft delete)",
     *     tags={"Task Dependencies"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="taskDependency", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=204,
     *         description="Dependência removida com sucesso"
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Dependência não encontrada")
     * )
     */
    public function destroy(Request $request, TaskDependency $taskDependency): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($taskDependency->task->company_id === $companyId, 403);
        abort_unless($user->projects()->whereKey($taskDependency->task->project_id)->exists(), 403);

        $taskDependency->delete();

        return response()->json(null, 204);
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/tasks/{task}/dependencies",
     *     summary="Atualizar dependências de uma tarefa em bulk",
     *     description="Adiciona ou remove múltiplas dependências de uma tarefa específica em uma única operação. Permite adicionar novas dependências e remover existentes.",
     *     tags={"Task Dependencies"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="task", in="path", required=true, @OA\Schema(type="integer"), description="ID da tarefa"),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="add", type="array", @OA\Items(type="integer"), description="IDs das tarefas que esta tarefa dependerá (adicionar dependências)"),
     *             @OA\Property(property="remove", type="array", @OA\Items(type="integer"), description="IDs das dependências a serem removidas")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Dependências atualizadas com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="added", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="removed", type="integer", description="Número de dependências removidas")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Tarefa não encontrada"),
     *     @OA\Response(response=422, description="Erro de validação (ciclo detectado, etc)")
     * )
     */
    public function updateBulk(Request $request, \App\Models\Task $task): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($task->company_id === $companyId, 403);
        abort_unless($user->projects()->whereKey($task->project_id)->exists(), 403);

        $request->validate([
            'add' => ['sometimes', 'array'],
            'add.*' => ['integer', 'exists:tasks,id', 'different:task'],
            'remove' => ['sometimes', 'array'],
            'remove.*' => ['integer', 'exists:task_dependencies,id'],
        ]);

        $toAdd = $request->input('add', []);
        $toRemove = $request->input('remove', []);

        $errors = [];
        $added = [];

        // Validate all dependencies to add before creating any
        foreach ($toAdd as $index => $dependsOnTaskId) {
            $dependsOnTaskId = (int) $dependsOnTaskId;

            // Verify task belongs to the same project
            $dependsOnTask = \App\Models\Task::find($dependsOnTaskId);
            if (! $dependsOnTask || $dependsOnTask->project_id !== $task->project_id) {
                $errors["add.{$index}"] = ['A tarefa não pertence ao mesmo projeto.'];
                continue;
            }

            // Validate cycle detection
            $cycle = $this->dependencyService->detectCycleOnAdd($task->id, $dependsOnTaskId);
            if ($cycle !== null) {
                $cyclePath = implode(' -> ', $cycle);
                $errors["add.{$index}"] = [
                    sprintf(
                        'Esta dependência criaria um ciclo: %s.',
                        $cyclePath
                    ),
                ];
                continue;
            }

            // Check for duplicate
            $exists = TaskDependency::where('task_id', $task->id)
                ->where('depends_on_task_id', $dependsOnTaskId)
                ->exists();

            if ($exists) {
                $errors["add.{$index}"] = ['Esta dependência já existe.'];
            }
        }

        if (! empty($errors)) {
            Log::warning('Bulk dependency update failed validation', [
                'task_id' => $task->id,
                'errors' => $errors,
            ]);

            throw ValidationException::withMessages($errors);
        }

        try {
            DB::beginTransaction();

            // Remove dependencies
            $removedCount = 0;
            if (! empty($toRemove)) {
                $dependenciesToRemove = TaskDependency::whereIn('id', $toRemove)
                    ->where('task_id', $task->id)
                    ->get();

                foreach ($dependenciesToRemove as $dependency) {
                    $dependency->delete();
                    $removedCount++;
                }
            }

            // Add new dependencies
            foreach ($toAdd as $dependsOnTaskId) {
                $dependsOnTaskId = (int) $dependsOnTaskId;

                // Double-check it doesn't exist (race condition protection)
                $exists = TaskDependency::where('task_id', $task->id)
                    ->where('depends_on_task_id', $dependsOnTaskId)
                    ->exists();

                if (! $exists) {
                    try {
                        $taskDependency = TaskDependency::create([
                            'task_id' => $task->id,
                            'depends_on_task_id' => $dependsOnTaskId,
                        ]);
                        $added[] = [
                            'id' => $taskDependency->id,
                            'task_id' => $taskDependency->task_id,
                            'depends_on_task_id' => $taskDependency->depends_on_task_id,
                            'created_at' => $taskDependency->created_at,
                            'updated_at' => $taskDependency->updated_at,
                        ];
                    } catch (QueryException $e) {
                        // Handle unique constraint violation
                        if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'uk_task_dependencies_task_depends')) {
                            Log::warning('Duplicate dependency in bulk update', [
                                'task_id' => $task->id,
                                'depends_on_task_id' => $dependsOnTaskId,
                            ]);
                            // Skip this one, don't fail the whole operation
                            continue;
                        }
                        throw $e;
                    }
                }
            }

            DB::commit();

            return response()->json([
                'data' => [
                    'added' => $added,
                    'removed' => $removedCount,
                ],
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk dependency update failed', [
                'task_id' => $task->id,
                'error' => $e->getMessage(),
            ]);

            throw ValidationException::withMessages([
                'dependencies' => ['Erro ao atualizar dependências. Tente novamente.'],
            ]);
        }
    }
}
