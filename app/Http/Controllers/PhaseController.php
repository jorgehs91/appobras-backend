<?php

namespace App\Http\Controllers;

use App\Http\Requests\Phase\StorePhaseRequest;
use App\Http\Requests\Phase\UpdatePhaseRequest;
use App\Http\Resources\PhaseResource;
use App\Models\Phase;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Phases",
 *     description="Gerenciamento de fases do projeto"
 * )
 */
class PhaseController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/projects/{project}/phases",
     *     summary="Listar fases do projeto",
     *     description="Retorna todas as fases de um projeto com progresso calculado",
     *     tags={"Phases"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="project", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string", enum={"draft", "active", "archived"})),
     *     @OA\Response(response=200, description="Lista de fases com progresso")
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

        $query = Phase::query()
            ->where('project_id', $project->id)
            ->with('tasks')
            ->orderBy('sequence');

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        $phases = $query->get();

        return PhaseResource::collection($phases)->response();
    }

    /**
     * @OA\Post(
     *     path="/api/v1/projects/{project}/phases",
     *     summary="Criar fase",
     *     description="Cria uma nova fase no projeto",
     *     tags={"Phases"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="project", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Fundação"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="status", type="string", enum={"draft", "active", "archived"}),
     *             @OA\Property(property="sequence", type="integer", example=1),
     *             @OA\Property(property="color", type="string", example="#FF5733"),
     *             @OA\Property(property="planned_start_at", type="string", format="date"),
     *             @OA\Property(property="planned_end_at", type="string", format="date")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Fase criada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=422, description="Erro de validação")
     * )
     */
    public function store(StorePhaseRequest $request, Project $project): JsonResponse
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

        $phase = Phase::query()->create($payload);

        return (new PhaseResource($phase->load('tasks')))->response()->setStatusCode(201);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/phases/{phase}",
     *     summary="Atualizar fase",
     *     description="Atualiza informações de uma fase existente",
     *     tags={"Phases"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="phase", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="status", type="string", enum={"draft", "active", "archived"}),
     *             @OA\Property(property="sequence", type="integer"),
     *             @OA\Property(property="color", type="string", example="#FF5733"),
     *             @OA\Property(property="planned_start_at", type="string", format="date"),
     *             @OA\Property(property="planned_end_at", type="string", format="date"),
     *             @OA\Property(property="actual_start_at", type="string", format="date"),
     *             @OA\Property(property="actual_end_at", type="string", format="date")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Fase atualizada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Fase não encontrada"),
     *     @OA\Response(response=422, description="Erro de validação")
     * )
     */
    public function update(UpdatePhaseRequest $request, Phase $phase): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($phase->company_id === $companyId, 403);
        abort_unless($user->projects()->whereKey($phase->project_id)->exists(), 403);

        $phase->fill($request->validated());
        $phase->save();

        return (new PhaseResource($phase->load('tasks')))->response();
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/phases/{phase}",
     *     summary="Remover fase",
     *     description="Remove uma fase (soft delete). Não é possível remover fases que contêm tarefas",
     *     tags={"Phases"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="phase", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=204,
     *         description="Fase removida com sucesso"
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Fase não encontrada"),
     *     @OA\Response(
     *         response=422,
     *         description="Não é possível deletar uma fase que contém tarefas",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Não é possível deletar uma fase que contém tarefas.")
     *         )
     *     )
     * )
     */
    public function destroy(Request $request, Phase $phase): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($phase->company_id === $companyId, 403);
        abort_unless($user->projects()->whereKey($phase->project_id)->exists(), 403);

        // Check if phase has tasks
        if ($phase->tasks()->exists()) {
            return response()->json([
                'message' => 'Não é possível deletar uma fase que contém tarefas.',
            ], 422);
        }

        $phase->delete();

        return response()->json(null, 204);
    }
}

