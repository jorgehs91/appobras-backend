<?php

namespace App\Http\Controllers;

use App\Http\Requests\Document\StoreDocumentRequest;
use App\Http\Resources\DocumentResource;
use App\Models\File;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * @group Documentos
 *
 * Endpoints relacionados ao gerenciamento de documentos de projetos.
 *
 * @OA\Tag(
 *     name="Documents",
 *     description="Gerenciamento de documentos do projeto"
 * )
 */
class DocumentController extends Controller
{
    /**
     * Retorna o disk configurado para armazenar arquivos.
     * Por padrão usa 'local', mas pode ser alterado via env FILES_DISK.
     * Para usar S3, defina FILES_DISK=s3 no .env
     *
     * @return string
     */
    protected function getFilesDisk(): string
    {
        return config('filesystems.files_disk', 'local');
    }

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

        $documents = File::query()
            ->where('fileable_type', Project::class)
            ->where('fileable_id', $project->id)
            ->where('category', 'document')
            ->with('uploader')
            ->orderByDesc('created_at')
            ->get();

        return DocumentResource::collection($documents)->response();
    }

    /**
     * @OA\Post(
     *     path="/api/v1/projects/{project}/documents",
     *     summary="Upload de documento",
     *     description="Faz upload de um documento para o projeto",
     *     tags={"Documents"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="project", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"file"},
     *                 @OA\Property(
     *                     property="file",
     *                     type="string",
     *                     format="binary",
     *                     description="Arquivo a ser enviado (PDF, JPG, PNG, DOC, DOCX, XLS, XLSX - máx. 10MB)"
     *                 ),
     *                 @OA\Property(property="name", type="string", example="Planta Baixa")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Documento enviado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=422, description="Erro de validação")
     * )
     */
    public function store(StoreDocumentRequest $request, Project $project): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');
        $disk = $this->getFilesDisk();

        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($project->company_id === $companyId, 403);
        abort_unless($user->projects()->whereKey($project->id)->exists(), 403);

        $file = $request->file('file');
        $name = $request->input('name') ?? $file->getClientOriginalName();

        // Store file in storage/app/documents/project-{id}
        $path = $file->store("documents/project-{$project->id}", $disk);

        // Generate file URL (use Storage::url() if disk is public, or signed URL if private)
        $fileUrl = Storage::disk($disk)->url($path);

        $document = File::query()->create([
            'fileable_type' => Project::class,
            'fileable_id' => $project->id,
            'company_id' => $companyId,
            'project_id' => $project->id,
            'name' => $name,
            'path' => $path,
            'url' => $fileUrl,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'thumbnail_path' => null,
            'category' => 'document',
            'uploaded_by' => $user->id,
        ]);

        return (new DocumentResource($document->load('uploader')))->response()->setStatusCode(201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/documents/{document}",
     *     summary="Obter documento específico",
     *     description="Retorna um documento específico por ID. O usuário deve ter permissão para visualizar documentos do projeto.",
     *     tags={"Documents"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="document", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Documento encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Documento não encontrado")
     * )
     */
    public function show(Request $request, File $document): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($document->company_id === $companyId, 403);
        abort_unless($document->category === 'document', 403);
        abort_unless($document->fileable_type === Project::class, 403);
        abort_unless($user->projects()->whereKey($document->project_id)->exists(), 403);

        $document->load('uploader');

        return (new DocumentResource($document))->response();
    }

    /**
     * @OA\Get(
     *     path="/api/v1/documents/{document}/download",
     *     summary="Baixar arquivo do documento",
     *     description="Retorna o arquivo físico do documento para download. O usuário deve ter permissão para visualizar documentos do projeto.",
     *     tags={"Documents"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="document", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Arquivo do documento",
     *         @OA\MediaType(
     *             mediaType="application/octet-stream",
     *             @OA\Schema(type="string", format="binary")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Documento não encontrado")
     * )
     */
    public function download(Request $request, File $document): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');
        $disk = $this->getFilesDisk();

        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($document->company_id === $companyId, 403);
        abort_unless($document->category === 'document', 403);
        abort_unless($document->fileable_type === Project::class, 403);
        abort_unless($user->projects()->whereKey($document->project_id)->exists(), 403);

        abort_unless(Storage::disk($disk)->exists($document->path), 404, 'Arquivo não encontrado no storage');

        return Storage::disk($disk)->response($document->path, $document->name, [
            'Content-Type' => $document->mime_type,
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/documents/{document}",
     *     summary="Remover documento",
     *     description="Remove um documento (soft delete) e exclui o arquivo do storage",
     *     tags={"Documents"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="document", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=204,
     *         description="Documento removido com sucesso"
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Documento não encontrado")
     * )
     */
    public function destroy(Request $request, File $document): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');
        $disk = $this->getFilesDisk();

        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($document->company_id === $companyId, 403);
        abort_unless($document->category === 'document', 403);
        abort_unless($document->fileable_type === Project::class, 403);
        abort_unless($user->projects()->whereKey($document->project_id)->exists() || $document->uploaded_by === $user->id, 403);

        // Delete file from storage
        if (Storage::disk($disk)->exists($document->path)) {
            Storage::disk($disk)->delete($document->path);
        }

        $document->delete();

        return response()->json(null, 204);
    }
}
