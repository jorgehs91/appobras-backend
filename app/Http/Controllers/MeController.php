<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeController extends Controller
{
    public function switchCompany(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_id' => ['required', 'integer', 'exists:companies,id'],
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();

        abort_unless($user->companies()->whereKey($validated['company_id'])->exists(), 403);

        $user->current_company_id = (int) $validated['company_id'];
        $user->save();

        return response()->json(['message' => 'Company switched'], 200);
    }

    public function switchProject(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => ['required', 'integer', 'exists:projects,id'],
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);

        $project = \App\Models\Project::query()
            ->whereKey($validated['project_id'])
            ->where('company_id', $companyId)
            ->firstOrFail();

        $user->current_project_id = (int) $project->id;
        $user->save();

        return response()->json(['message' => 'Project switched'], 200);
    }
}


