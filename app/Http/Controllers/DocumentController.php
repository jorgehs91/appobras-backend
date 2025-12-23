<?php

namespace App\Http\Controllers;

use App\Http\Requests\Document\StoreDocumentRequest;
use App\Http\Resources\DocumentResource;
use App\Models\Document;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Tag(
 *     name="Documents",
 *     description="Gerenciamento de documentos do projeto"
 * )
 */
class DocumentController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/projects/{project}/documents",
     *     summary="Listar documentos do projeto",
     *     tags={"Documents"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="project", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Lista de documentos")
     * )
     */
    public function index(Request $request, Project $project): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($project->company_id === $companyId, 403);
        abort_unless($user->projects()->whereKey($project->id)->exists(), 403);

        $documents = Document::query()
            ->where('project_id', $project->id)
            ->with('uploader')
            ->orderByDesc('created_at')
            ->get();

        return DocumentResource::collection($documents)->response();
    }

    /**
     * Store a newly uploaded document.
     */
    public function store(StoreDocumentRequest $request, Project $project): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($project->company_id === $companyId, 403);
        abort_unless($user->projects()->whereKey($project->id)->exists(), 403);

        $file = $request->file('file');
        $name = $request->input('name') ?? $file->getClientOriginalName();

        // Store file in storage/app/documents/project-{id}
        $path = $file->store("documents/project-{$project->id}", 'local');

        // Generate file URL (use Storage::url() if disk is public, or signed URL if private)
        $fileUrl = Storage::url($path);

        $document = Document::query()->create([
            'company_id' => $companyId,
            'project_id' => $project->id,
            'name' => $name,
            'file_path' => $path,
            'file_url' => $fileUrl,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'uploaded_by' => $user->id,
        ]);

        return (new DocumentResource($document->load('uploader')))->response()->setStatusCode(201);
    }

    /**
     * Remove the specified document (soft delete + delete file).
     */
    public function destroy(Request $request, Document $document): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($document->company_id === $companyId, 403);
        abort_unless($user->projects()->whereKey($document->project_id)->exists() || $document->uploaded_by === $user->id, 403);

        // Delete file from storage
        if (Storage::exists($document->file_path)) {
            Storage::delete($document->file_path);
        }

        $document->delete();

        return response()->json(null, 204);
    }
}

