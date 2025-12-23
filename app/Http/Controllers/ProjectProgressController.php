<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Progress",
 *     description="Progresso e estatísticas"
 * )
 */
class ProjectProgressController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/projects/{project}/progress",
     *     summary="Progresso detalhado do projeto",
     *     description="Retorna o progresso do projeto calculado com base nas fases e tarefas",
     *     tags={"Progress"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="project", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200, 
     *         description="Progresso do projeto",
     *         @OA\JsonContent(
     *             @OA\Property(property="project_id", type="integer"),
     *             @OA\Property(property="project_progress_percent", type="integer"),
     *             @OA\Property(property="phases", type="array", @OA\Items())
     *         )
     *     )
     * )
     */
    public function show(Request $request, Project $project): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($project->company_id === $companyId, 403);
        abort_unless($user->projects()->whereKey($project->id)->exists(), 403);

        // Load active phases with their tasks
        $phases = $project->phases()
            ->where('status', 'active')
            ->with('tasks')
            ->orderBy('sequence')
            ->get();

        $phasesData = $phases->map(function ($phase) {
            return [
                'id' => $phase->id,
                'name' => $phase->name,
                'status' => $phase->status->value,
                'progress_percent' => $phase->progress_percent,
                'tasks_count' => $phase->tasks()->count(),
            ];
        });

        return response()->json([
            'project_id' => $project->id,
            'project_progress_percent' => $project->progress_percent,
            'phases' => $phasesData,
        ]);
    }
}

