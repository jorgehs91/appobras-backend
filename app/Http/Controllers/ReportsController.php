<?php

namespace App\Http\Controllers;

use App\Jobs\TasksCsvExportJob;
use App\Jobs\ProgressCsvExportJob;
use App\Jobs\PvxrCsvExportJob;
use App\Jobs\ExpensesCsvExportJob;
use App\Jobs\PurchaseRequestsCsvExportJob;
use App\Jobs\PurchaseOrdersCsvExportJob;
use App\Jobs\PaymentsCsvExportJob;
use App\Jobs\ContractorsCsvExportJob;
use App\Jobs\DocumentsCsvExportJob;
use App\Jobs\LicensesCsvExportJob;
use App\Jobs\AuditLogsCsvExportJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @OA\Tag(
 *     name="Reports",
 *     description="Exportação de relatórios em formato CSV"
 * )
 */
class ReportsController extends Controller
{
    /**
     * Supported report types and their corresponding job classes.
     *
     * @var array<string, string>
     */
    protected const REPORT_TYPES = [
        'tasks' => TasksCsvExportJob::class,
        'progress' => ProgressCsvExportJob::class,
        'pvxrv' => PvxrCsvExportJob::class,
        'expenses' => ExpensesCsvExportJob::class,
        'purchase-requests' => PurchaseRequestsCsvExportJob::class,
        'purchase-orders' => PurchaseOrdersCsvExportJob::class,
        'payments' => PaymentsCsvExportJob::class,
        'contractors' => ContractorsCsvExportJob::class,
        'documents' => DocumentsCsvExportJob::class,
        'licenses' => LicensesCsvExportJob::class,
        'audit-logs' => AuditLogsCsvExportJob::class,
    ];

    /**
     * @OA\Post(
     *     path="/api/v1/reports/{type}/export",
     *     summary="Solicitar exportação de relatório CSV",
     *     description="Inicia a geração assíncrona de um relatório CSV. O usuário receberá uma notificação quando o arquivo estiver pronto para download.",
     *     tags={"Reports"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(
     *         name="type",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", enum={"tasks", "progress", "pvxrv", "expenses", "purchase-requests", "purchase-orders", "payments", "contractors", "documents", "licenses", "audit-logs"}),
     *         description="Tipo de relatório a ser exportado"
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="project_id", type="integer", description="ID do projeto (opcional)"),
     *             @OA\Property(property="phase_id", type="integer", description="ID da fase (opcional, apenas para relatórios de tarefas)"),
     *             @OA\Property(property="status", type="string", description="Status para filtrar (opcional)"),
     *             @OA\Property(property="assignee_id", type="integer", description="ID do responsável (opcional, apenas para relatórios de tarefas)"),
     *             @OA\Property(property="start_date", type="string", format="date", description="Data inicial do período (opcional)"),
     *             @OA\Property(property="end_date", type="string", format="date", description="Data final do período (opcional)"),
     *             @OA\Property(property="overdue", type="boolean", description="Apenas itens atrasados (opcional, apenas para relatórios de tarefas)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=202,
     *         description="Exportação iniciada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Exportação iniciada. Você receberá uma notificação quando o arquivo estiver pronto."),
     *             @OA\Property(property="report_type", type="string", example="tasks")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Tipo de relatório inválido"),
     *     @OA\Response(response=403, description="Sem permissão")
     * )
     */
    public function export(Request $request, string $type): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless(isset(self::REPORT_TYPES[$type]), 400, 'Tipo de relatório inválido');

        $jobClass = self::REPORT_TYPES[$type];

        // Extract filters from request (varies by report type)
        $filters = $request->only([
            'project_id',
            'phase_id',
            'status',
            'assignee_id',
            'start_date',
            'end_date',
            'overdue',
            'category',
            'cost_item_id',
            'include_archived',
        ]);

        // Remove null values
        $filters = array_filter($filters, fn ($value) => $value !== null);

        // Dispatch the export job
        $projectId = $filters['project_id'] ?? null;
        unset($filters['project_id']);

        $jobClass::dispatch($user->id, $companyId, $projectId, $filters);

        return response()->json([
            'message' => 'Exportação iniciada. Você receberá uma notificação quando o arquivo estiver pronto.',
            'report_type' => $type,
        ], 202);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/reports/download/{filename}",
     *     summary="Download de arquivo CSV exportado",
     *     description="Baixa um arquivo CSV previamente exportado. O arquivo deve ter sido gerado pelo usuário autenticado.",
     *     tags={"Reports"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(
     *         name="filename",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Nome do arquivo CSV a ser baixado"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Arquivo CSV",
     *         @OA\MediaType(
     *             mediaType="text/csv",
     *             @OA\Schema(type="string", format="binary")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão para baixar este arquivo"),
     *     @OA\Response(response=404, description="Arquivo não encontrado")
     * )
     */
    public function download(Request $request, string $filename): StreamedResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);

        $disk = config('filesystems.default', 'local');
        $filePath = "exports/{$filename}";

        // Verify file exists
        abort_unless(Storage::disk($disk)->exists($filePath), 404, 'Arquivo não encontrado');

        // Verify the file belongs to a notification for this user
        // This ensures users can only download their own exports
        $notification = \App\Models\Notification::where('user_id', $user->id)
            ->where('type', 'export.completed')
            ->whereJsonContains('data->filename', $filename)
            ->first();

        abort_unless($notification, 403, 'Você não tem permissão para baixar este arquivo');

        // Return file download
        return Storage::disk($disk)->response($filePath, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}

