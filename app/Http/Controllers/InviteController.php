<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanyInvite;
use App\Models\ProjectInvite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Role;

/**
 * @OA\Tag(
 *     name="Invites",
 *     description="Gerenciamento de convites para empresas"
 * )
 */
class InviteController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/companies/{company}/invites",
     *     summary="Criar convite",
     *     description="Cria um convite para adicionar um usuário à empresa",
     *     tags={"Invites"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="X-Company-Id",
     *         in="header",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="company",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="usuario@example.com"),
     *             @OA\Property(property="role_name", type="string", example="Admin Obra")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Convite criado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="abc123def456...")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Empresa não encontrada"),
     *     @OA\Response(response=422, description="Erro de validação")
     * )
     */
    public function create(Request $request, Company $company): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'role_name' => ['nullable', 'string'],
        ]);

        $token = Str::random(40);

        $invite = CompanyInvite::query()->create([
            'company_id' => $company->id,
            'email' => (string) $validated['email'],
            'role_name' => $validated['role_name'] ?? null,
            'token' => $token,
            'expires_at' => now()->addDays(7),
        ]);

        return response()->json(['token' => $invite->token], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/invites/{token}/accept",
     *     summary="Aceitar convite",
     *     description="Aceita um convite para ingressar em uma empresa",
     *     tags={"Invites"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="token",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Token do convite"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Convite aceito com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invite accepted")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Convite não encontrado ou expirado")
     * )
     */
    public function accept(Request $request, string $token): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $invite = CompanyInvite::query()
            ->where('token', $token)
            ->where(function ($q) { $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()); })
            ->firstOrFail();

        // vincular
        $user->companies()->syncWithoutDetaching([$invite->company_id]);

        // dar papel sugerido, se houver
        app(PermissionRegistrar::class)->setPermissionsTeamId($invite->company_id);
        if ($invite->role_name) {
            $user->assignRole(Role::findByName($invite->role_name, 'sanctum'));
        }

        // marcar aceito
        $invite->accepted_at = now();
        $invite->save();

        return response()->json(['message' => 'Invite accepted'], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/invites/project/{token}/accept",
     *     summary="Aceitar convite de projeto",
     *     description="Aceita um convite para ingressar em um projeto",
     *     tags={"Invites"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="token",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Token do convite de projeto"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Convite de projeto aceito com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Project invite accepted")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Convite não encontrado ou expirado")
     * )
     */
    public function acceptProjectInvite(Request $request, string $token): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $invite = ProjectInvite::query()
            ->where('token', $token)
            ->where('user_id', $user->id)
            ->where(function ($q) { $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()); })
            ->whereNull('accepted_at')
            ->firstOrFail();

        $project = $invite->project;

        // Verificar se o usuário já é membro (idempotência - caso tenha sido adicionado por outro meio)
        if ($project->users()->whereKey($user->id)->exists()) {
            // Se já é membro, apenas atualizar role se necessário e marcar convite como aceito
            $project->users()->updateExistingPivot($user->id, [
                'role' => $invite->role,
            ]);
            $invite->accepted_at = now();
            $invite->save();

            return response()->json([
                'message' => 'Convite aceito. Você já era membro deste projeto.',
            ], 200);
        }

        // Garantir que o usuário pertence à empresa do projeto
        $user->companies()->syncWithoutDetaching([$project->company_id]);

        // Adicionar como membro do projeto com a role do convite
        $project->users()->attach($user->id, [
            'role' => $invite->role,
            'joined_at' => now(),
        ]);

        // Marcar convite como aceito
        $invite->accepted_at = now();
        $invite->save();

        return response()->json(['message' => 'Project invite accepted'], 200);
    }
}


