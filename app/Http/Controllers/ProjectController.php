<?php

namespace App\Http\Controllers;

use App\Http\Requests\Project\StoreProjectRequest;
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

        $query = Project::query()->where('company_id', $companyId)->orderBy('name');

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

        $project = Project::query()->create([
            'company_id' => $companyId,
            'name' => $request->string('name')->toString(),
            'description' => $request->string('description')->toString() ?: null,
        ]);

        return (new ProjectResource($project))->response()->setStatusCode(201);
    }
}


