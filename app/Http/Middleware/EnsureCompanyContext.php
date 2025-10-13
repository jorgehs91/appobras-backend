<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;

class EnsureCompanyContext
{
    public function handle(Request $request, Closure $next)
    {
        $companyId = (int) $request->header('X-Company-Id');

        $user = $request->user();
        if (! $user || ! $companyId || ! $user->companies()->whereKey($companyId)->exists()) {
            abort(403);
        }

        app(PermissionRegistrar::class)->setPermissionsTeamId($companyId);

        return $next($request);
    }
}


