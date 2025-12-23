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
     * Store a newly created phase.
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
     * Update the specified phase.
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
     * Remove the specified phase (soft delete).
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

