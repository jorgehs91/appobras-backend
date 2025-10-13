<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\CompanyInvite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Role;

class InviteController extends Controller
{
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
}


