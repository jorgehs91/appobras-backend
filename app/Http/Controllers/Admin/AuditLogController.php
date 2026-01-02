<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Admin
 *
 * Endpoints administrativos para visualização de logs de auditoria.
 *
 * @OA\Tag(
 *     name="Admin",
 *     description="Gerenciamento administrativo de auditoria"
 * )
 */
class AuditLogController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/admin/audit-logs",
     *     summary="Listar logs de auditoria",
     *     description="Retorna os logs de auditoria com filtros opcionais por projeto, usuário, datas, tipo de entidade e ação",
     *     tags={"Admin"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="X-Company-Id",
     *         in="header",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="project_id",
     *         in="query",
     *         required=false,
     *         description="Filtrar por ID do projeto",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         required=false,
     *         description="Filtrar por ID do usuário",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="from",
     *         in="query",
     *         required=false,
     *         description="Data inicial (formato: Y-m-d)",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="to",
     *         in="query",
     *         required=false,
     *         description="Data final (formato: Y-m-d)",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="entity_type",
     *         in="query",
     *         required=false,
     *         description="Filtrar por tipo de entidade (ex: App\\Models\\Project)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="action",
     *         in="query",
     *         required=false,
     *         description="Filtrar por ação (created, updated, deleted, restored)",
     *         @OA\Schema(type="string", enum={"created", "updated", "deleted", "restored"})
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Número da página",
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Itens por página",
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de logs de auditoria",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="user_id", type="integer", nullable=true, example=1),
     *                     @OA\Property(property="auditable_id", type="integer", example=1),
     *                     @OA\Property(property="auditable_type", type="string", example="App\\Models\\Project"),
     *                     @OA\Property(property="event", type="string", example="created"),
     *                     @OA\Property(property="old_values", type="object", nullable=true),
     *                     @OA\Property(property="new_values", type="object", nullable=true),
     *                     @OA\Property(property="ip", type="string", nullable=true, example="127.0.0.1"),
     *                     @OA\Property(property="user_agent", type="string", nullable=true),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(
     *                         property="user",
     *                         type="object",
     *                         nullable=true,
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="name", type="string"),
     *                         @OA\Property(property="email", type="string")
     *                     ),
     *                     @OA\Property(
     *                         property="auditable",
     *                         type="object",
     *                         nullable=true,
     *                         description="Entidade relacionada (polimórfica)"
     *                     )
     *                 )
     *             ),
     *             @OA\Property(property="current_page", type="integer"),
     *             @OA\Property(property="last_page", type="integer"),
     *             @OA\Property(property="per_page", type="integer"),
     *             @OA\Property(property="total", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);

        $query = AuditLog::with(['user', 'auditable'])
            ->byCompany($companyId);

        // Filtro por projeto
        if ($request->has('project_id')) {
            $query->byProject((int) $request->input('project_id'));
        }

        // Filtro por usuário
        if ($request->has('user_id')) {
            $query->where('user_id', (int) $request->input('user_id'));
        }

        // Filtro por data inicial
        if ($request->has('from')) {
            $query->where('created_at', '>=', $request->input('from'));
        }

        // Filtro por data final
        if ($request->has('to')) {
            $query->where('created_at', '<=', $request->input('to').' 23:59:59');
        }

        // Filtro por tipo de entidade
        if ($request->has('entity_type')) {
            $query->where('auditable_type', $request->input('entity_type'));
        }

        // Filtro por ação
        if ($request->has('action')) {
            $query->where('event', $request->input('action'));
        }

        $perPage = min((int) $request->input('per_page', 15), 100); // Máximo 100 por página

        $logs = $query->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json($logs);
    }
}
