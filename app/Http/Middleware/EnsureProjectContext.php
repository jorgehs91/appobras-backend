<?php

namespace App\Http\Middleware;

use App\Models\Project;
use Closure;
use Illuminate\Http\Request;

class EnsureProjectContext
{
    public function handle(Request $request, Closure $next)
    {
        $projectIdHeader = (int) $request->header('X-Project-Id');

        // Se não houver header, segue sem restringir por projeto
        if (! $projectIdHeader) {
            return $next($request);
        }

        /** @var \App\Models\User|null $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        // Requer contexto de company válido previamente
        if (! $user || ! $companyId || ! $user->companies()->whereKey($companyId)->exists()) {
            abort(403);
        }

        $project = Project::query()
            ->whereKey($projectIdHeader)
            ->where('company_id', $companyId)
            ->first();

        if (! $project) {
            abort(403);
        }

        // Checar membership do usuário no projeto
        if (! $user->projects()->whereKey($project->id)->exists()) {
            abort(403);
        }

        // Anexa o projeto resolvido na request para consumo downstream
        $request->attributes->set('project', $project);

        return $next($request);
    }
}


