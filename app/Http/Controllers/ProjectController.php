<?php

namespace App\Http\Controllers;

use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Company;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
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
            $payload['status'] = 'planned';
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
        $isCompleted = ($data['status'] ?? $project->status) === 'completed';
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
}


