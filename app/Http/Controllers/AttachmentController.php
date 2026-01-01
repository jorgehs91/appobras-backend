<?php

namespace App\Http\Controllers;

use App\Http\Requests\Attachment\StoreAttachmentRequest;
use App\Http\Resources\AttachmentResource;
use App\Models\File;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Tag(
 *     name="Attachments",
 *     description="Gerenciamento de anexos de tarefas"
 * )
 */
class AttachmentController extends Controller
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
     *     path="/api/v1/tasks/{task}/attachments",
     *     summary="Listar anexos da tarefa",
     *     description="Retorna todos os anexos de uma tarefa",
     *     tags={"Attachments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="task", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de anexos",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Tarefa não encontrada")
     * )
     */
    public function index(Request $request, Task $task): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($task->company_id === $companyId, 403);
        abort_unless($user->projects()->whereKey($task->project_id)->exists(), 403);

        $attachments = File::query()
            ->where('fileable_type', Task::class)
            ->where('fileable_id', $task->id)
            ->where('category', 'attachment')
            ->with('uploader')
            ->orderByDesc('created_at')
            ->get();

        return AttachmentResource::collection($attachments)->response();
    }

    /**
     * @OA\Post(
     *     path="/api/v1/tasks/{task}/attachments",
     *     summary="Upload de anexo",
     *     description="Faz upload de um anexo para a tarefa",
     *     tags={"Attachments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="task", in="path", required=true, @OA\Schema(type="integer")),
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
     *                     description="Arquivo a ser enviado (PDF, JPG, PNG, DOC, DOCX, XLS, XLSX, ZIP, RAR - máx. 10MB)"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Anexo enviado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=422, description="Erro de validação")
     * )
     */
    public function store(StoreAttachmentRequest $request, Task $task): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');
        $disk = $this->getFilesDisk();

        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($task->company_id === $companyId, 403);
        abort_unless($user->projects()->whereKey($task->project_id)->exists(), 403);

        $file = $request->file('file');
        $filename = $file->getClientOriginalName();

        // Store file in storage/app/attachments/task-{id}
        $path = $file->store("attachments/task-{$task->id}", $disk);

        $attachment = File::query()->create([
            'fileable_type' => Task::class,
            'fileable_id' => $task->id,
            'company_id' => $task->company_id,
            'project_id' => $task->project_id,
            'name' => $filename,
            'path' => $path,
            'url' => null,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'thumbnail_path' => null,
            'category' => 'attachment',
            'uploaded_by' => $user->id,
        ]);

        return (new AttachmentResource($attachment->load('uploader')))->response()->setStatusCode(201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/attachments/{attachment}",
     *     summary="Obter anexo específico",
     *     description="Retorna um anexo específico por ID. O usuário deve ter permissão para visualizar anexos da tarefa.",
     *     tags={"Attachments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="attachment", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Anexo encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Anexo não encontrado")
     * )
     */
    public function show(Request $request, File $attachment): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($attachment->company_id === $companyId, 403);
        abort_unless($attachment->category === 'attachment', 403);
        abort_unless($attachment->fileable_type === Task::class, 403);
        abort_unless($user->projects()->whereKey($attachment->project_id)->exists(), 403);

        $attachment->load('uploader');

        return (new AttachmentResource($attachment))->response();
    }

    /**
     * @OA\Get(
     *     path="/api/v1/attachments/{attachment}/download",
     *     summary="Baixar arquivo do anexo",
     *     description="Retorna o arquivo físico do anexo para download. O usuário deve ter permissão para visualizar anexos da tarefa.",
     *     tags={"Attachments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="attachment", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Arquivo do anexo",
     *         @OA\MediaType(
     *             mediaType="application/octet-stream",
     *             @OA\Schema(type="string", format="binary")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Anexo não encontrado")
     * )
     */
    public function download(Request $request, File $attachment): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');
        $disk = $this->getFilesDisk();

        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($attachment->company_id === $companyId, 403);
        abort_unless($attachment->category === 'attachment', 403);
        abort_unless($attachment->fileable_type === Task::class, 403);
        abort_unless($user->projects()->whereKey($attachment->project_id)->exists(), 403);

        abort_unless(Storage::disk($disk)->exists($attachment->path), 404, 'Arquivo não encontrado no storage');

        return Storage::disk($disk)->response($attachment->path, $attachment->name, [
            'Content-Type' => $attachment->mime_type,
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/attachments/{attachment}",
     *     summary="Remover anexo",
     *     description="Remove um anexo (soft delete) e exclui o arquivo do storage",
     *     tags={"Attachments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="attachment", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=204,
     *         description="Anexo removido com sucesso"
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Anexo não encontrado")
     * )
     */
    public function destroy(Request $request, File $attachment): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');
        $disk = $this->getFilesDisk();

        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($attachment->company_id === $companyId, 403);
        abort_unless($attachment->category === 'attachment', 403);
        abort_unless($attachment->fileable_type === Task::class, 403);
        abort_unless($user->projects()->whereKey($attachment->project_id)->exists() || $attachment->uploaded_by === $user->id, 403);

        // Delete file from storage
        if (Storage::disk($disk)->exists($attachment->path)) {
            Storage::disk($disk)->delete($attachment->path);
        }

        // Delete thumbnail if exists
        if ($attachment->thumbnail_path && Storage::disk($disk)->exists($attachment->thumbnail_path)) {
            Storage::disk($disk)->delete($attachment->thumbnail_path);
        }

        $attachment->delete();

        return response()->json(null, 204);
    }
}
