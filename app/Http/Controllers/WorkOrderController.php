<?php

namespace App\Http\Controllers;

use App\Enums\WorkOrderStatus;
use App\Http\Requests\WorkOrder\StoreWorkOrderRequest;
use App\Http\Requests\WorkOrder\UpdateWorkOrderRequest;
use App\Http\Resources\WorkOrderResource;
use App\Models\Contract;
use App\Models\Contractor;
use App\Models\WorkOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="WorkOrders",
 *     description="Gerenciamento de ordens de serviço de empreiteiros"
 * )
 */
class WorkOrderController extends Controller
{
    /**
     * Verifica se o usuário tem permissão para acessar recursos de ordens de serviço.
     */
    protected function checkBudgetPermission($user): void
    {
        abort_unless(
            $user->hasBudgetAccess(),
            403,
            'Apenas usuários com role Financeiro ou Admin Obra podem acessar recursos de ordens de serviço.'
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/contractors/{contractor}/work-orders",
     *     summary="Listar ordens de serviço do empreiteiro",
     *     description="Retorna todas as ordens de serviço de um empreiteiro",
     *     tags={"WorkOrders"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="contractor", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string", enum={"draft", "approved", "completed", "canceled"}), description="Filtrar por status"),
     *     @OA\Parameter(name="contract_id", in="query", required=false, @OA\Schema(type="integer"), description="Filtrar por contrato"),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de ordens de serviço",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/WorkOrder"))
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Empreiteiro não encontrado")
     * )
     */
    public function index(Request $request, Contractor $contractor): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        $this->checkBudgetPermission($user);
        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($contractor->company_id === $companyId, 403);

        $query = WorkOrder::query()
            ->whereHas('contract', function ($q) use ($contractor) {
                $q->where('contractor_id', $contractor->id);
            })
            ->with(['contract']);

        // Filter by status if provided
        if ($request->has('status')) {
            $status = WorkOrderStatus::tryFrom($request->input('status'));
            if ($status) {
                $query->where('status', $status);
            }
        }

        // Filter by contract if provided
        if ($request->has('contract_id')) {
            $query->where('contract_id', $request->input('contract_id'));
        }

        $workOrders = $query->orderByDesc('created_at')->get();

        return WorkOrderResource::collection($workOrders)->response();
    }

    /**
     * @OA\Post(
     *     path="/api/v1/contractors/{contractor}/work-orders",
     *     summary="Criar ordem de serviço",
     *     description="Cria uma nova ordem de serviço para o empreiteiro",
     *     tags={"WorkOrders"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="contractor", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"contract_id", "description", "value", "status"},
     *             @OA\Property(property="contract_id", type="integer", example=1),
     *             @OA\Property(property="description", type="string", example="Execução de fundação"),
     *             @OA\Property(property="value", type="number", format="float", example=25000.00),
     *             @OA\Property(property="due_date", type="string", format="date", nullable=true, example="2025-06-30"),
     *             @OA\Property(property="status", type="string", enum={"draft", "approved", "completed", "canceled"}, example="draft")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Ordem de serviço criada com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", ref="#/components/schemas/WorkOrder")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Empreiteiro não encontrado"),
     *     @OA\Response(response=422, description="Erro de validação")
     * )
     */
    public function store(StoreWorkOrderRequest $request, Contractor $contractor): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        $this->checkBudgetPermission($user);
        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($contractor->company_id === $companyId, 403);

        // Verify contract belongs to contractor and company
        $contractId = $request->input('contract_id');
        $contract = Contract::find($contractId);
        abort_unless($contract && $contract->contractor_id === $contractor->id, 404, 'Contrato não pertence ao empreiteiro.');
        abort_unless($contract->project->company_id === $companyId, 403, 'Contrato não pertence à empresa.');

        $payload = $request->validated();
        $payload['created_by'] = $user->id;
        $payload['updated_by'] = $user->id;

        $workOrder = WorkOrder::query()->create($payload);

        return (new WorkOrderResource($workOrder->load(['contract'])))->response()->setStatusCode(201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/contractors/{contractor}/work-orders/{workOrder}",
     *     summary="Visualizar ordem de serviço",
     *     description="Retorna detalhes de uma ordem de serviço específica",
     *     tags={"WorkOrders"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="contractor", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="workOrder", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Detalhes da ordem de serviço",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", ref="#/components/schemas/WorkOrder")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Ordem de serviço não encontrada")
     * )
     */
    public function show(Request $request, Contractor $contractor, WorkOrder $workOrder): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        $this->checkBudgetPermission($user);
        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($contractor->company_id === $companyId, 403);
        abort_unless($workOrder->contract->contractor_id === $contractor->id, 404, 'Ordem de serviço não pertence ao empreiteiro.');

        return (new WorkOrderResource($workOrder->load(['contract', 'payments'])))->response();
    }

    /**
     * @OA\Put(
     *     path="/api/v1/contractors/{contractor}/work-orders/{workOrder}",
     *     summary="Atualizar ordem de serviço",
     *     description="Atualiza informações de uma ordem de serviço existente (apenas se status for draft ou canceled)",
     *     tags={"WorkOrders"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="contractor", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="workOrder", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="contract_id", type="integer", example=1),
     *             @OA\Property(property="description", type="string", example="Execução de fundação"),
     *             @OA\Property(property="value", type="number", format="float", example=25000.00),
     *             @OA\Property(property="due_date", type="string", format="date", nullable=true, example="2025-06-30"),
     *             @OA\Property(property="status", type="string", enum={"draft", "approved", "completed", "canceled"}, example="draft")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ordem de serviço atualizada com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", ref="#/components/schemas/WorkOrder")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Ordem de serviço não encontrada"),
     *     @OA\Response(response=422, description="Erro de validação")
     * )
     */
    public function update(UpdateWorkOrderRequest $request, Contractor $contractor, WorkOrder $workOrder): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        $this->checkBudgetPermission($user);
        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($contractor->company_id === $companyId, 403);
        abort_unless($workOrder->contract->contractor_id === $contractor->id, 404, 'Ordem de serviço não pertence ao empreiteiro.');

        // Check authorization using policy
        $this->authorize('update', $workOrder);

        // Verify contract belongs to contractor and company if contract_id is being updated
        if ($request->has('contract_id')) {
            $contractId = $request->input('contract_id');
            $contract = Contract::find($contractId);
            abort_unless($contract && $contract->contractor_id === $contractor->id, 404, 'Contrato não pertence ao empreiteiro.');
            abort_unless($contract->project->company_id === $companyId, 403, 'Contrato não pertence à empresa.');
        }

        $payload = $request->validated();
        $workOrder->fill($payload);
        $workOrder->updated_by = $user->id;
        $workOrder->save();

        return (new WorkOrderResource($workOrder->load(['contract'])))->response();
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/contractors/{contractor}/work-orders/{workOrder}",
     *     summary="Remover ordem de serviço",
     *     description="Remove uma ordem de serviço (soft delete, apenas se status for draft)",
     *     tags={"WorkOrders"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="contractor", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="workOrder", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=204,
     *         description="Ordem de serviço removida com sucesso"
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Ordem de serviço não encontrada")
     * )
     */
    public function destroy(Request $request, Contractor $contractor, WorkOrder $workOrder): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        $this->checkBudgetPermission($user);
        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($contractor->company_id === $companyId, 403);
        abort_unless($workOrder->contract->contractor_id === $contractor->id, 404, 'Ordem de serviço não pertence ao empreiteiro.');

        // Check authorization using policy
        $this->authorize('delete', $workOrder);

        $workOrder->delete();

        return response()->json(null, 204);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/contractors/{contractor}/work-orders/{workOrder}/approve",
     *     summary="Aprovar ordem de serviço",
     *     description="Aprova uma ordem de serviço em draft",
     *     tags={"WorkOrders"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="contractor", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="workOrder", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Ordem de serviço aprovada com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", ref="#/components/schemas/WorkOrder"),
     *             @OA\Property(property="message", type="string", example="Ordem de serviço aprovada com sucesso.")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Ordem de serviço não encontrada"),
     *     @OA\Response(response=422, description="Ordem de serviço não pode ser aprovada")
     * )
     */
    public function approve(Request $request, Contractor $contractor, WorkOrder $workOrder): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        $this->checkBudgetPermission($user);
        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($contractor->company_id === $companyId, 403);
        abort_unless($workOrder->contract->contractor_id === $contractor->id, 404, 'Ordem de serviço não pertence ao empreiteiro.');

        // Check authorization using policy
        $this->authorize('approve', $workOrder);

        // Update status to approved
        $workOrder->status = WorkOrderStatus::approved;
        $workOrder->updated_by = $user->id;
        $workOrder->save();

        // Reload with relationships
        $workOrder->load(['contract']);

        return (new WorkOrderResource($workOrder))->response()->setData([
            'data' => new WorkOrderResource($workOrder),
            'message' => 'Ordem de serviço aprovada com sucesso.',
        ]);
    }
}

