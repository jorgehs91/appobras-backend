<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PermissionResource;
use App\Http\Resources\RoleResource;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Admin\RoleAssignmentRequest;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): JsonResponse
    {
        $roles = Role::query()->select(['id', 'name', 'guard_name'])->orderBy('name')->get();
        return RoleResource::collection($roles)->response();
    }

    public function permissions(): JsonResponse
    {
        $permissions = Permission::query()->select(['id', 'name', 'guard_name'])->orderBy('name')->get();
        return PermissionResource::collection($permissions)->response();
    }

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

    public function revoke(RoleAssignmentRequest $request, Role $role): JsonResponse
    {
        $validated = $request->validated();

        $userModel = config('auth.providers.users.model');
        $user = $userModel::query()->findOrFail($validated['user_id']);
        $user->removeRole($role);

        return response()->json(['message' => 'Role revoked'], 200);
    }
}


