<?php

namespace App\Http\Controllers;

use App\Enums\ProjectMemberRole;
use App\Http\Requests\ProjectMember\StoreProjectMemberRequest;
use App\Http\Requests\ProjectMember\UpdateProjectMemberRequest;
use App\Http\Resources\ProjectMemberResource;
use App\Jobs\SendProjectInvitationJob;
use App\Models\Project;
use App\Models\ProjectInvite;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * @OA\Tag(
 *     name="Project Members",
 *     description="Gerenciamento de membros de projeto"
 * )
 */
class ProjectMembersController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/projects/{project}/members",
     *     summary="Listar membros do projeto",
     *     description="Retorna todos os membros de um projeto com suas roles",
     *     tags={"Project Members"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="project", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Lista de membros do projeto"),
     *     @OA\Response(response=403, description="Sem permissão")
     * )
     */
    public function index(Request $request, Project $project): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($project->company_id === $companyId, 403);
        $this->authorize('viewAny', $project);

        $members = $project->users()
            ->withPivot(['role', 'joined_at', 'preferences'])
            ->orderBy('name')
            ->get();

        return ProjectMemberResource::collection($members)->response();
    }

    /**
     * @OA\Post(
     *     path="/api/v1/projects/{project}/members",
     *     summary="Enviar convite para adicionar membro ao projeto",
     *     description="Cria um convite para adicionar um usuário ao projeto. O usuário será adicionado apenas após aceitar o convite via email.",
     *     tags={"Project Members"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="project", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "role"},
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="role", type="string", enum={"Manager", "Engenheiro", "Fiscal", "Coordenador", "Viewer"}, example="Engenheiro")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Convite criado e enviado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="invite_token", type="string"),
     *             @OA\Property(property="expires_at", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=422, description="Erro de validação ou convite já existe")
     * )
     */
    public function store(StoreProjectMemberRequest $request, Project $project): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($project->company_id === $companyId, 403);
        $this->authorize('create', $project);

        $validated = $request->validated();
        $memberUser = User::query()->findOrFail($validated['user_id']);

        // Verificar se o usuário já é membro
        if ($project->users()->whereKey($memberUser->id)->exists()) {
            return response()->json([
                'message' => 'O usuário já é membro deste projeto.',
            ], 422);
        }

        // Verificar se já existe convite pendente para este usuário neste projeto
        $existingInvite = ProjectInvite::query()
            ->where('project_id', $project->id)
            ->where('user_id', $memberUser->id)
            ->whereNull('accepted_at')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->first();

        if ($existingInvite) {
            return response()->json([
                'message' => 'Já existe um convite pendente para este usuário neste projeto.',
                'invite_token' => $existingInvite->token,
            ], 422);
        }

        // Garantir que o usuário pertence à empresa
        $memberUser->companies()->syncWithoutDetaching([$companyId]);

        // Criar convite (NÃO adicionar ao projeto ainda - só será adicionado quando aceitar)
        $token = Str::random(40);
        $invite = ProjectInvite::query()->create([
            'project_id' => $project->id,
            'user_id' => $memberUser->id,
            'role' => $validated['role'],
            'token' => $token,
            'expires_at' => now()->addDays(7),
        ]);

        // Disparar job para enviar email de convite
        SendProjectInvitationJob::dispatch($invite, $project, $memberUser);

        return response()->json([
            'message' => 'Convite enviado com sucesso. O usuário será adicionado ao projeto após aceitar o convite.',
            'invite_token' => $invite->token,
            'expires_at' => $invite->expires_at->toISOString(),
        ], 201);
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/projects/{project}/members/{userId}",
     *     summary="Atualizar role de membro",
     *     description="Atualiza a role de um membro do projeto",
     *     tags={"Project Members"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="project", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="userId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"role"},
     *             @OA\Property(property="role", type="string", enum={"Manager", "Engenheiro", "Fiscal", "Coordenador", "Viewer"}, example="Manager")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Role atualizada com sucesso"),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Membro não encontrado"),
     *     @OA\Response(response=422, description="Erro de validação")
     * )
     */
    public function update(UpdateProjectMemberRequest $request, Project $project, int $userId): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($project->company_id === $companyId, 403);
        $this->authorize('update', $project);

        // Verificar se o usuário é membro do projeto
        $memberUser = $project->users()->whereKey($userId)->first();
        abort_unless($memberUser, 404, 'Membro não encontrado neste projeto.');

        $validated = $request->validated();

        // Atualizar role no pivot
        $project->users()->updateExistingPivot($userId, [
            'role' => $validated['role'],
        ]);

        // Recarregar o relacionamento
        $memberUser = $project->users()->whereKey($userId)->first();

        return (new ProjectMemberResource($memberUser))->response();
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/projects/{project}/members/{userId}",
     *     summary="Remover membro do projeto",
     *     description="Remove um usuário do projeto",
     *     tags={"Project Members"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="project", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="userId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Membro removido com sucesso"),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Membro não encontrado")
     * )
     */
    public function destroy(Request $request, Project $project, int $userId): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($project->company_id === $companyId, 403);
        $this->authorize('delete', $project);

        // Verificar se o usuário é membro do projeto
        $isMember = $project->users()->whereKey($userId)->exists();
        abort_unless($isMember, 404, 'Membro não encontrado neste projeto.');

        // Remover do projeto
        $project->users()->detach($userId);

        return response()->json([
            'message' => 'Membro removido do projeto com sucesso.',
        ]);
    }
}
