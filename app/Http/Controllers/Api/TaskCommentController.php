<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaskComment\StoreTaskCommentRequest;
use App\Http\Requests\TaskComment\UpdateTaskCommentRequest;
use App\Http\Resources\TaskCommentResource;
use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Tarefas
 *
 * Endpoints relacionados ao gerenciamento de comentários em tarefas.
 *
 * @OA\Tag(
 *     name="Task Comments",
 *     description="Gerenciamento de comentários em tarefas"
 * )
 */
class TaskCommentController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/tasks/{task}/comments",
     *     summary="Listar comentários da tarefa",
     *     description="Retorna todos os comentários de uma tarefa ordenados cronologicamente",
     *     tags={"Task Comments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="task", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="order_by", in="query", @OA\Schema(type="string", enum={"asc", "desc"}, default="asc")),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de comentários",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Tarefa não encontrada")
     * )
     */
    public function index(Request $request, Task $task): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($task->company_id === $companyId, 403);
        abort_unless($user->projects()->whereKey($task->project_id)->exists(), 403);

        $orderBy = $request->input('order_by', 'asc');
        $orderDirection = $orderBy === 'desc' ? 'desc' : 'asc';

        $comments = TaskComment::query()
            ->where('task_id', $task->id)
            ->with('user')
            ->orderBy('created_at', $orderDirection)
            ->get();

        return TaskCommentResource::collection($comments)->response();
    }

    /**
     * @OA\Post(
     *     path="/api/v1/tasks/{task}/comments",
     *     summary="Criar comentário",
     *     description="Cria um novo comentário na tarefa",
     *     tags={"Task Comments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="task", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"body"},
     *             @OA\Property(property="body", type="string", example="Este é um comentário sobre a tarefa.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Comentário criado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=422, description="Erro de validação")
     * )
     */
    public function store(StoreTaskCommentRequest $request, Task $task): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($task->company_id === $companyId, 403);
        abort_unless($user->projects()->whereKey($task->project_id)->exists(), 403);

        $comment = TaskComment::query()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'body' => $request->validated()['body'],
        ]);

        return (new TaskCommentResource($comment->load('user')))->response()->setStatusCode(201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/tasks/{task}/comments/{comment}",
     *     summary="Obter comentário específico",
     *     description="Retorna um comentário específico por ID",
     *     tags={"Task Comments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="task", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="comment", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Comentário encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Comentário não encontrado")
     * )
     */
    public function show(Request $request, Task $task, TaskComment $comment): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($task->company_id === $companyId, 403);
        abort_unless($comment->task_id === $task->id, 404, 'Comentário não pertence à tarefa');
        abort_unless($user->projects()->whereKey($task->project_id)->exists(), 403);

        $comment->load('user');

        return (new TaskCommentResource($comment))->response();
    }

    /**
     * @OA\Put(
     *     path="/api/v1/tasks/{task}/comments/{comment}",
     *     summary="Atualizar comentário",
     *     description="Atualiza um comentário existente. Apenas o autor pode atualizar.",
     *     tags={"Task Comments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="task", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="comment", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="body", type="string", example="Comentário atualizado"),
     *             @OA\Property(property="reactions", type="object", example={"like": 5, "love": 2})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Comentário atualizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Comentário não encontrado")
     * )
     */
    public function update(UpdateTaskCommentRequest $request, Task $task, TaskComment $comment): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($task->company_id === $companyId, 403);
        abort_unless($comment->task_id === $task->id, 404, 'Comentário não pertence à tarefa');
        abort_unless($comment->user_id === $user->id, 403, 'Apenas o autor pode atualizar o comentário');
        abort_unless($user->projects()->whereKey($task->project_id)->exists(), 403);

        $validated = $request->validated();
        $comment->update($validated);

        return (new TaskCommentResource($comment->load('user')))->response();
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/tasks/{task}/comments/{comment}",
     *     summary="Remover comentário",
     *     description="Remove um comentário (soft delete). Apenas o autor ou administrador pode remover.",
     *     tags={"Task Comments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="task", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="comment", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=204,
     *         description="Comentário removido com sucesso"
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Comentário não encontrado")
     * )
     */
    public function destroy(Request $request, Task $task, TaskComment $comment): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($task->company_id === $companyId, 403);
        abort_unless($comment->task_id === $task->id, 404, 'Comentário não pertence à tarefa');
        abort_unless($user->projects()->whereKey($task->project_id)->exists(), 403);

        // Apenas o autor pode deletar (ou admin no futuro)
        abort_unless($comment->user_id === $user->id, 403, 'Apenas o autor pode remover o comentário');

        $comment->delete();

        return response()->json(null, 204);
    }
}
