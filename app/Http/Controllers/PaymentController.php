<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Http\Requests\Payment\StorePaymentRequest;
use App\Http\Requests\Payment\UpdatePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Contract;
use App\Models\Contractor;
use App\Models\Payment;
use App\Models\WorkOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Empreiteiros
 *
 * Endpoints relacionados ao gerenciamento de pagamentos de empreiteiros.
 *
 * @OA\Tag(
 *     name="Payments",
 *     description="Gerenciamento de pagamentos de empreiteiros"
 * )
 */
class PaymentController extends Controller
{
    /**
     * Verifica se o usuário tem permissão para acessar recursos de pagamentos.
     */
    protected function checkBudgetPermission($user): void
    {
        abort_unless(
            $user->hasBudgetAccess(),
            403,
            'Apenas usuários com role Financeiro ou Admin Obra podem acessar recursos de pagamentos.'
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/contractors/{contractor}/payments",
     *     summary="Listar pagamentos do empreiteiro",
     *     description="Retorna todos os pagamentos de um empreiteiro (de contratos e ordens de serviço)",
     *     tags={"Payments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="contractor", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string", enum={"pending", "paid", "canceled", "overdue"}), description="Filtrar por status"),
     *     @OA\Parameter(name="payable_type", in="query", required=false, @OA\Schema(type="string", enum={"App\\Models\\Contract", "App\\Models\\WorkOrder"}), description="Filtrar por tipo"),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de pagamentos",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Payment"))
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

        // Get payments from contracts and work orders of this contractor
        $contractIds = $contractor->contracts()->pluck('id');
        $workOrderIds = WorkOrder::query()
            ->whereHas('contract', function ($q) use ($contractor) {
                $q->where('contractor_id', $contractor->id);
            })
            ->pluck('id');

        $query = Payment::query()
            ->where(function ($q) use ($contractIds, $workOrderIds) {
                $q->where(function ($subQ) use ($contractIds) {
                    $subQ->where('payable_type', 'App\\Models\\Contract')
                        ->whereIn('payable_id', $contractIds);
                })->orWhere(function ($subQ) use ($workOrderIds) {
                    $subQ->where('payable_type', 'App\\Models\\WorkOrder')
                        ->whereIn('payable_id', $workOrderIds);
                });
            })
            ->with(['payable']);

        // Filter by status if provided
        if ($request->has('status')) {
            $status = PaymentStatus::tryFrom($request->input('status'));
            if ($status) {
                $query->byStatus($status);
            }
        }

        // Filter by payable_type if provided
        if ($request->has('payable_type')) {
            $query->where('payable_type', $request->input('payable_type'));
        }

        $payments = $query->orderByDesc('due_date')->orderByDesc('created_at')->get();

        return PaymentResource::collection($payments)->response();
    }

    /**
     * @OA\Post(
     *     path="/api/v1/contractors/{contractor}/payments",
     *     summary="Criar pagamento",
     *     description="Cria um novo pagamento para um contrato ou ordem de serviço do empreiteiro",
     *     tags={"Payments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="contractor", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"payable_type", "payable_id", "amount", "due_date", "status"},
     *             @OA\Property(property="payable_type", type="string", enum={"App\\Models\\Contract", "App\\Models\\WorkOrder"}, example="App\\Models\\Contract"),
     *             @OA\Property(property="payable_id", type="integer", example=1),
     *             @OA\Property(property="amount", type="number", format="float", example=10000.00),
     *             @OA\Property(property="due_date", type="string", format="date", example="2025-06-30"),
     *             @OA\Property(property="status", type="string", enum={"pending", "paid", "canceled", "overdue"}, example="pending"),
     *             @OA\Property(property="paid_at", type="string", format="date-time", nullable=true),
     *             @OA\Property(property="payment_proof_path", type="string", nullable=true, example="payments/proof-123.pdf")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Pagamento criado com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", ref="#/components/schemas/Payment")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Empreiteiro não encontrado"),
     *     @OA\Response(response=422, description="Erro de validação")
     * )
     */
    public function store(StorePaymentRequest $request, Contractor $contractor): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        $this->checkBudgetPermission($user);
        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($contractor->company_id === $companyId, 403);

        // Verify payable belongs to contractor
        $payableType = $request->input('payable_type');
        $payableId = $request->input('payable_id');

        if ($payableType === 'App\\Models\\Contract') {
            $payable = Contract::find($payableId);
            abort_unless($payable && $payable->contractor_id === $contractor->id, 404, 'Contrato não pertence ao empreiteiro.');
            abort_unless($payable->project->company_id === $companyId, 403, 'Contrato não pertence à empresa.');
        } elseif ($payableType === 'App\\Models\\WorkOrder') {
            $payable = WorkOrder::find($payableId);
            abort_unless($payable, 404, 'Ordem de serviço não encontrada.');
            abort_unless($payable->contract->contractor_id === $contractor->id, 404, 'Ordem de serviço não pertence ao empreiteiro.');
            abort_unless($payable->contract->project->company_id === $companyId, 403, 'Ordem de serviço não pertence à empresa.');
        } else {
            abort(422, 'Tipo de pagável inválido.');
        }

        $payload = $request->validated();
        $payload['created_by'] = $user->id;
        $payload['updated_by'] = $user->id;

        $payment = Payment::query()->create($payload);

        return (new PaymentResource($payment->load(['payable'])))->response()->setStatusCode(201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/contractors/{contractor}/payments/{payment}",
     *     summary="Visualizar pagamento",
     *     description="Retorna detalhes de um pagamento específico",
     *     tags={"Payments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="contractor", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="payment", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Detalhes do pagamento",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", ref="#/components/schemas/Payment")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Pagamento não encontrado")
     * )
     */
    public function show(Request $request, Contractor $contractor, Payment $payment): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        $this->checkBudgetPermission($user);
        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($contractor->company_id === $companyId, 403);

        // Verify payment belongs to contractor
        $this->verifyPaymentBelongsToContractor($payment, $contractor, $companyId);

        return (new PaymentResource($payment->load(['payable'])))->response();
    }

    /**
     * @OA\Put(
     *     path="/api/v1/contractors/{contractor}/payments/{payment}",
     *     summary="Atualizar pagamento",
     *     description="Atualiza informações de um pagamento existente",
     *     tags={"Payments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="contractor", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="payment", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="amount", type="number", format="float", example=10000.00),
     *             @OA\Property(property="due_date", type="string", format="date", example="2025-06-30"),
     *             @OA\Property(property="status", type="string", enum={"pending", "paid", "canceled", "overdue"}, example="paid"),
     *             @OA\Property(property="paid_at", type="string", format="date-time", nullable=true),
     *             @OA\Property(property="payment_proof_path", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pagamento atualizado com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", ref="#/components/schemas/Payment")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Pagamento não encontrado"),
     *     @OA\Response(response=422, description="Erro de validação")
     * )
     */
    public function update(UpdatePaymentRequest $request, Contractor $contractor, Payment $payment): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        $this->checkBudgetPermission($user);
        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($contractor->company_id === $companyId, 403);

        // Verify payment belongs to contractor
        $this->verifyPaymentBelongsToContractor($payment, $contractor, $companyId);

        // If updating payable_type or payable_id, verify the new payable belongs to contractor
        if ($request->has('payable_type') || $request->has('payable_id')) {
            $payableType = $request->input('payable_type', $payment->payable_type);
            $payableId = $request->input('payable_id', $payment->payable_id);

            if ($payableType === 'App\\Models\\Contract') {
                $payable = Contract::find($payableId);
                abort_unless($payable && $payable->contractor_id === $contractor->id, 404, 'Contrato não pertence ao empreiteiro.');
                abort_unless($payable->project->company_id === $companyId, 403, 'Contrato não pertence à empresa.');
            } elseif ($payableType === 'App\\Models\\WorkOrder') {
                $payable = WorkOrder::find($payableId);
                abort_unless($payable, 404, 'Ordem de serviço não encontrada.');
                abort_unless($payable->contract->contractor_id === $contractor->id, 404, 'Ordem de serviço não pertence ao empreiteiro.');
                abort_unless($payable->contract->project->company_id === $companyId, 403, 'Ordem de serviço não pertence à empresa.');
            }
        }

        $payload = $request->validated();
        $payment->fill($payload);
        $payment->updated_by = $user->id;
        $payment->save();

        return (new PaymentResource($payment->load(['payable'])))->response();
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/contractors/{contractor}/payments/{payment}",
     *     summary="Remover pagamento",
     *     description="Remove um pagamento (soft delete)",
     *     tags={"Payments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="contractor", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="payment", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=204,
     *         description="Pagamento removido com sucesso"
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Pagamento não encontrado")
     * )
     */
    public function destroy(Request $request, Contractor $contractor, Payment $payment): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        $this->checkBudgetPermission($user);
        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($contractor->company_id === $companyId, 403);

        // Verify payment belongs to contractor
        $this->verifyPaymentBelongsToContractor($payment, $contractor, $companyId);

        $payment->delete();

        return response()->json(null, 204);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/contractors/{contractor}/payments/pending",
     *     summary="Listar pagamentos pendentes do empreiteiro",
     *     description="Retorna todos os pagamentos pendentes de um empreiteiro usando query otimizada",
     *     tags={"Payments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="contractor", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="payable_type", in="query", required=false, @OA\Schema(type="string", enum={"App\\Models\\Contract", "App\\Models\\WorkOrder"}), description="Filtrar por tipo"),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de pagamentos pendentes",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Payment")),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="total_amount", type="number", format="float", example=50000.00),
     *                 @OA\Property(property="count", type="integer", example=5)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Empreiteiro não encontrado")
     * )
     */
    public function pending(Request $request, Contractor $contractor): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        $this->checkBudgetPermission($user);
        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($contractor->company_id === $companyId, 403);

        // Get payments from contracts and work orders of this contractor using aggregation
        $contractIds = $contractor->contracts()->pluck('id');
        $workOrderIds = WorkOrder::query()
            ->whereHas('contract', function ($q) use ($contractor) {
                $q->where('contractor_id', $contractor->id);
            })
            ->pluck('id');

        $query = Payment::query()
            ->where(function ($q) use ($contractIds, $workOrderIds) {
                $q->where(function ($subQ) use ($contractIds) {
                    $subQ->where('payable_type', 'App\\Models\\Contract')
                        ->whereIn('payable_id', $contractIds);
                })->orWhere(function ($subQ) use ($workOrderIds) {
                    $subQ->where('payable_type', 'App\\Models\\WorkOrder')
                        ->whereIn('payable_id', $workOrderIds);
                });
            })
            ->pending()
            ->with(['payable']);

        // Filter by payable_type if provided
        if ($request->has('payable_type')) {
            $query->where('payable_type', $request->input('payable_type'));
        }

        // Get total amount using aggregation
        $totalAmount = (clone $query)->sum('amount');
        $count = (clone $query)->count();

        $payments = $query->orderBy('due_date')->orderByDesc('created_at')->get();

        return PaymentResource::collection($payments)->additional([
            'meta' => [
                'total_amount' => (float) $totalAmount,
                'count' => $count,
            ],
        ])->response();
    }

    /**
     * Verify that a payment belongs to the given contractor.
     */
    protected function verifyPaymentBelongsToContractor(Payment $payment, Contractor $contractor, int $companyId): void
    {
        if ($payment->payable_type === 'App\\Models\\Contract') {
            $contract = Contract::find($payment->payable_id);
            abort_unless($contract && $contract->contractor_id === $contractor->id, 404, 'Pagamento não pertence ao empreiteiro.');
            abort_unless($contract->project->company_id === $companyId, 403, 'Pagamento não pertence à empresa.');
        } elseif ($payment->payable_type === 'App\\Models\\WorkOrder') {
            $workOrder = WorkOrder::find($payment->payable_id);
            abort_unless($workOrder, 404, 'Ordem de serviço não encontrada.');
            abort_unless($workOrder->contract->contractor_id === $contractor->id, 404, 'Pagamento não pertence ao empreiteiro.');
            abort_unless($workOrder->contract->project->company_id === $companyId, 403, 'Pagamento não pertence à empresa.');
        } else {
            abort(404, 'Tipo de pagável inválido.');
        }
    }
}

