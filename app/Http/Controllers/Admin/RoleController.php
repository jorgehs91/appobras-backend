<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PermissionResource;
use App\Http\Resources\RoleResource;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Admin\RoleAssignmentRequest;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * @group Admin
 *
 * Endpoints administrativos para gerenciamento de roles e permissões.
 *
 * @OA\Tag(
 *     name="Admin",
 *     description="Gerenciamento administrativo de roles e permissões"
 * )
 */
class RoleController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/admin/roles",
     *     summary="Listar roles",
     *     description="Retorna todas as roles disponíveis no sistema",
     *     tags={"Admin"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="X-Company-Id",
     *         in="header",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de roles",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Admin Obra"),
     *                     @OA\Property(property="guard_name", type="string", example="sanctum")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão")
     * )
     */
    public function index(): JsonResponse
    {
        $roles = Role::query()->select(['id', 'name', 'guard_name'])->orderBy('name')->get();
        return RoleResource::collection($roles)->response();
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/permissions",
     *     summary="Listar permissões",
     *     description="Retorna todas as permissões disponíveis no sistema",
     *     tags={"Admin"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="X-Company-Id",
     *         in="header",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de permissões",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="users.update"),
     *                     @OA\Property(property="guard_name", type="string", example="sanctum")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão")
     * )
     */
    public function permissions(): JsonResponse
    {
        $permissions = Permission::query()->select(['id', 'name', 'guard_name'])->orderBy('name')->get();
        return PermissionResource::collection($permissions)->response();
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/roles/{role}/assign",
     *     summary="Atribuir role",
     *     description="Atribui uma role a um usuário",
     *     tags={"Admin"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="X-Company-Id",
     *         in="header",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="role",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id"},
     *             @OA\Property(property="user_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role atribuída com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Role assigned")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Role ou usuário não encontrado"),
     *     @OA\Response(response=422, description="Erro de validação")
     * )
     */
    public function assign(RoleAssignmentRequest $request, Role $role): JsonResponse
    {
        $validated = $request->validated();

        $userModel = config('auth.providers.users.model');
        $user = $userModel::query()->findOrFail($validated['user_id']);
        // garantir vínculo do usuário à empresa atual (teams)
        $companyId = (int) $request->header('X-Company-Id');
        if ($companyId > 0) {
            $user->companies()->syncWithoutDetaching([$companyId]);
        }
        $user->assignRole($role);

        return response()->json(['message' => 'Role assigned'], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/roles/{role}/revoke",
     *     summary="Revogar role",
     *     description="Revoga uma role de um usuário",
     *     tags={"Admin"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="X-Company-Id",
     *         in="header",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="role",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id"},
     *             @OA\Property(property="user_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Role revogada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Role revoked")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Role ou usuário não encontrado"),
     *     @OA\Response(response=422, description="Erro de validação")
     * )
     */
    public function revoke(RoleAssignmentRequest $request, Role $role): JsonResponse
    {
        $validated = $request->validated();

        $userModel = config('auth.providers.users.model');
        $user = $userModel::query()->findOrFail($validated['user_id']);
        $user->removeRole($role);

        return response()->json(['message' => 'Role revoked'], 200);
    }
}


