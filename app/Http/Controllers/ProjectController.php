<?php

namespace App\Http\Controllers;

use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Company;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Projects",
 *     description="Gerenciamento de projetos/obras"
 * )
 */
class ProjectController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/projects",
     *     summary="Listar projetos",
     *     description="Retorna os projetos da empresa que o usuário tem acesso",
     *     tags={"Projects"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="X-Company-Id",
     *         in="header",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="X-Project-Id",
     *         in="header",
     *         required=false,
     *         description="Filtrar por projeto específico",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de projetos",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="description", type="string"),
     *                     @OA\Property(property="status", type="string"),
     *                     @OA\Property(property="address", type="string"),
     *                     @OA\Property(property="start_date", type="string", format="date"),
     *                     @OA\Property(property="end_date", type="string", format="date"),
     *                     @OA\Property(property="planned_budget_amount", type="number")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="Usuário não pertence à empresa")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);

        $query = Project::query()
            ->where('company_id', $companyId)
            ->whereHas('users', function ($q) use ($user): void {
                $q->whereKey($user->id);
            })
            ->orderBy('name');

        $projectId = (int) $request->header('X-Project-Id');
        if ($projectId) {
            $query->whereKey($projectId);
        }

        $projects = $query->get();

        return ProjectResource::collection($projects)->response();
    }

    /**
     * @OA\Post(
     *     path="/api/v1/projects",
     *     summary="Criar projeto",
     *     description="Cria um novo projeto/obra na empresa e atribui o criador como Manager",
     *     tags={"Projects"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="X-Company-Id",
     *         in="header",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Edifício Residencial ABC"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="address", type="string", example="Rua das Flores, 123"),
     *             @OA\Property(property="start_date", type="string", format="date"),
     *             @OA\Property(property="end_date", type="string", format="date"),
     *             @OA\Property(property="planned_budget_amount", type="number", example=500000.00)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Projeto criado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Usuário não pertence à empresa"),
     *     @OA\Response(response=422, description="Erro de validação")
     * )
     */
    public function store(StoreProjectRequest $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);

        $payload = $request->validated();

        // Garantir tenancy: company_id sempre do header/tenant atual
        $payload['company_id'] = $companyId;

        // Regras de datas: já validadas no Request; aqui poderíamos impor defaults
        if (! isset($payload['status'])) {
            $payload['status'] = \App\Enums\ProjectStatus::planning->value;
        }

        // Definir manager como o usuário autenticado
        $payload['manager_user_id'] = $user->id;

        $project = Project::query()->create($payload);

        // Vincular criador como membro do projeto com papel Manager
        $user->projects()->syncWithoutDetaching([$project->id => [
            'role' => 'Manager',
            'joined_at' => now(),
        ]]);

        return (new ProjectResource($project))->response()->setStatusCode(201);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/projects/{project}",
     *     summary="Atualizar projeto",
     *     description="Atualiza informações de um projeto existente",
     *     tags={"Projects"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="X-Company-Id",
     *         in="header",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="project",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="status", type="string", enum={"planning", "in_progress", "on_hold", "completed", "canceled"}),
     *             @OA\Property(property="address", type="string"),
     *             @OA\Property(property="start_date", type="string", format="date"),
     *             @OA\Property(property="end_date", type="string", format="date")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Projeto atualizado com sucesso"
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Projeto não encontrado")
     * )
     */
    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        // Tenancy + membership no projeto
        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($project->company_id === $companyId, 403);
        abort_unless($user->projects()->whereKey($project->id)->exists(), 403);

        $data = $request->validated();

        // Bloquear mudanças de cronograma quando completed (a não ser por perfil autorizado - placeholder)
        $isCompleted = ($data['status'] ?? ($project->status?->value ?? null)) === \App\Enums\ProjectStatus::completed->value;
        if ($project->status === 'completed') {
            unset($data['start_date'], $data['end_date'], $data['actual_start_date'], $data['actual_end_date']);
        }

        // Ao marcar completed, set actual_end_date se vazio
        if ($isCompleted && (! $project->actual_end_date) && empty($data['actual_end_date'])) {
            $data['actual_end_date'] = now()->toDateString();
        }

        $project->fill($data);
        $project->save();

        return (new ProjectResource($project))->response();
    }

    /**
     * @OA\Get(
     *     path="/api/v1/projects/{project}",
     *     summary="Exibir projeto",
     *     description="Retorna detalhes de um projeto específico",
     *     tags={"Projects"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="X-Company-Id",
     *         in="header",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="project",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalhes do projeto",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Projeto não encontrado")
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

        return (new ProjectResource($project))->response();
    }
}


