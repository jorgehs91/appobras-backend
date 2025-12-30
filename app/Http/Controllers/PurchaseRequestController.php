<?php

namespace App\Http\Controllers;

use App\Enums\PurchaseRequestStatus;
use App\Http\Requests\PurchaseRequest\StorePurchaseRequestRequest;
use App\Http\Requests\PurchaseRequest\UpdatePurchaseRequestRequest;
use App\Http\Resources\PurchaseRequestResource;
use App\Models\Project;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(
 *     name="PurchaseRequests",
 *     description="Gerenciamento de requisições de compra"
 * )
 */
class PurchaseRequestController extends Controller
{
    /**
     * Verifica se o usuário tem permissão para acessar recursos de requisições de compra.
     */
    protected function checkBudgetPermission($user): void
    {
        abort_unless(
            $user->hasBudgetAccess(),
            403,
            'Apenas usuários com role Financeiro ou Admin Obra podem acessar recursos de requisições de compra.'
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/projects/{project}/purchase-requests",
     *     summary="Listar requisições de compra do projeto",
     *     description="Retorna todas as requisições de compra de um projeto",
     *     tags={"PurchaseRequests"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="project", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string", enum={"draft", "submitted", "approved", "rejected"}), description="Filtrar por status"),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de requisições de compra",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/PurchaseRequest"))
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Projeto não encontrado")
     * )
     */
    public function index(Request $request, Project $project): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        $this->checkBudgetPermission($user);
        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($project->company_id === $companyId, 403);
        abort_unless($user->projects()->whereKey($project->id)->exists(), 403);

        $query = PurchaseRequest::query()
            ->where('project_id', $project->id)
            ->with(['supplier', 'items', 'purchaseOrder']);

        // Filter by status if provided
        if ($request->has('status')) {
            $status = PurchaseRequestStatus::tryFrom($request->input('status'));
            if ($status) {
                $query->where('status', $status);
            }
        }

        $purchaseRequests = $query->orderByDesc('created_at')->get();

        return PurchaseRequestResource::collection($purchaseRequests)->response();
    }

    /**
     * @OA\Get(
     *     path="/api/v1/purchase-requests/{purchaseRequest}",
     *     summary="Exibir requisição de compra",
     *     description="Retorna detalhes de uma requisição de compra específica",
     *     tags={"PurchaseRequests"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="purchaseRequest", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Detalhes da requisição de compra",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", ref="#/components/schemas/PurchaseRequest")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Requisição de compra não encontrada")
     * )
     */
    public function show(Request $request, PurchaseRequest $purchaseRequest): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        $this->checkBudgetPermission($user);
        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($purchaseRequest->project->company_id === $companyId, 403);
        abort_unless($user->projects()->whereKey($purchaseRequest->project_id)->exists(), 403);

        $purchaseRequest->load(['supplier', 'items', 'purchaseOrder', 'project']);

        return (new PurchaseRequestResource($purchaseRequest))->response();
    }

    /**
     * @OA\Post(
     *     path="/api/v1/projects/{project}/purchase-requests",
     *     summary="Criar requisição de compra",
     *     description="Cria uma nova requisição de compra no projeto com itens",
     *     tags={"PurchaseRequests"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="project", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"supplier_id", "items"},
     *             @OA\Property(property="supplier_id", type="integer", example=1),
     *             @OA\Property(property="status", type="string", enum={"draft"}, example="draft", description="Status inicial (apenas draft permitido)"),
     *             @OA\Property(property="notes", type="string", nullable=true, example="Observações sobre a requisição"),
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="cost_item_id", type="integer", nullable=true, example=1),
     *                     @OA\Property(property="description", type="string", example="Material de construção"),
     *                     @OA\Property(property="quantity", type="integer", example=10),
     *                     @OA\Property(property="unit_price", type="number", format="float", example=150.00)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Requisição de compra criada com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", ref="#/components/schemas/PurchaseRequest")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Projeto não encontrado"),
     *     @OA\Response(response=422, description="Erro de validação")
     * )
     */
    public function store(StorePurchaseRequestRequest $request, Project $project): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        $this->checkBudgetPermission($user);
        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($project->company_id === $companyId, 403);
        abort_unless($user->projects()->whereKey($project->id)->exists(), 403);

        return DB::transaction(function () use ($request, $project, $user) {
            $data = $request->validated();
            $data['project_id'] = $project->id;
            $data['status'] = PurchaseRequestStatus::draft;

            $items = $data['items'];
            unset($data['items']);

            $purchaseRequest = PurchaseRequest::query()->create($data);

            // Create items
            foreach ($items as $itemData) {
                PurchaseRequestItem::query()->create([
                    'purchase_request_id' => $purchaseRequest->id,
                    'cost_item_id' => $itemData['cost_item_id'] ?? null,
                    'description' => $itemData['description'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                ]);
            }

            // Reload with relationships
            $purchaseRequest->load(['supplier', 'items', 'project']);

            return (new PurchaseRequestResource($purchaseRequest))->response()->setStatusCode(201);
        });
    }

    /**
     * @OA\Put(
     *     path="/api/v1/purchase-requests/{purchaseRequest}",
     *     summary="Atualizar requisição de compra",
     *     description="Atualiza uma requisição de compra existente (apenas se status for draft ou rejected)",
     *     tags={"PurchaseRequests"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="purchaseRequest", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="supplier_id", type="integer", example=1),
     *             @OA\Property(property="notes", type="string", nullable=true),
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", description="ID do item existente (para atualizar)"),
     *                     @OA\Property(property="cost_item_id", type="integer", nullable=true),
     *                     @OA\Property(property="description", type="string"),
     *                     @OA\Property(property="quantity", type="integer"),
     *                     @OA\Property(property="unit_price", type="number", format="float")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Requisição de compra atualizada com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", ref="#/components/schemas/PurchaseRequest")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Requisição de compra não encontrada"),
     *     @OA\Response(response=422, description="Erro de validação ou requisição não pode ser editada")
     * )
     */
    public function update(UpdatePurchaseRequestRequest $request, PurchaseRequest $purchaseRequest): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        $this->checkBudgetPermission($user);
        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($purchaseRequest->project->company_id === $companyId, 403);
        abort_unless($user->projects()->whereKey($purchaseRequest->project_id)->exists(), 403);

        // Check if PR can be edited
        if (! $purchaseRequest->canBeEdited()) {
            return response()->json([
                'message' => 'Não é possível editar uma requisição de compra com status "' . $purchaseRequest->status->value . '".',
            ], 422);
        }

        return DB::transaction(function () use ($request, $purchaseRequest) {
            $data = $request->validated();

            // Handle items if provided
            if (isset($data['items'])) {
                $items = $data['items'];
                unset($data['items']);

                // Get existing item IDs
                $existingItemIds = $purchaseRequest->items()->pluck('id')->toArray();
                $updatedItemIds = [];

                foreach ($items as $itemData) {
                    if (isset($itemData['id']) && in_array($itemData['id'], $existingItemIds, true)) {
                        // Update existing item
                        $item = PurchaseRequestItem::query()->find($itemData['id']);
                        $item->update([
                            'cost_item_id' => $itemData['cost_item_id'] ?? null,
                            'description' => $itemData['description'],
                            'quantity' => $itemData['quantity'],
                            'unit_price' => $itemData['unit_price'],
                        ]);
                        $updatedItemIds[] = $item->id;
                    } else {
                        // Create new item
                        $newItem = PurchaseRequestItem::query()->create([
                            'purchase_request_id' => $purchaseRequest->id,
                            'cost_item_id' => $itemData['cost_item_id'] ?? null,
                            'description' => $itemData['description'],
                            'quantity' => $itemData['quantity'],
                            'unit_price' => $itemData['unit_price'],
                        ]);
                        $updatedItemIds[] = $newItem->id;
                    }
                }

                // Delete items that were not included in the update
                $itemsToDelete = array_diff($existingItemIds, $updatedItemIds);
                if (! empty($itemsToDelete)) {
                    PurchaseRequestItem::query()->whereIn('id', $itemsToDelete)->delete();
                }
            }

            // Update PR fields
            $purchaseRequest->fill($data);
            $purchaseRequest->save();

            // Reload with relationships
            $purchaseRequest->load(['supplier', 'items', 'project']);

            return (new PurchaseRequestResource($purchaseRequest))->response();
        });
    }

    /**
     * @OA\Post(
     *     path="/api/v1/purchase-requests/{purchaseRequest}/submit",
     *     summary="Submeter requisição de compra",
     *     description="Submete uma requisição de compra em draft para aprovação",
     *     tags={"PurchaseRequests"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="purchaseRequest", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Requisição de compra submetida com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", ref="#/components/schemas/PurchaseRequest")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Requisição de compra não encontrada"),
     *     @OA\Response(response=422, description="Requisição não pode ser submetida")
     * )
     */
    public function submit(Request $request, PurchaseRequest $purchaseRequest): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        $this->checkBudgetPermission($user);
        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($purchaseRequest->project->company_id === $companyId, 403);

        // Check authorization using policy
        $this->authorize('submit', $purchaseRequest);

        // Validate that PR has items
        if ($purchaseRequest->items()->count() === 0) {
            return response()->json([
                'message' => 'Não é possível submeter uma requisição de compra sem itens.',
            ], 422);
        }

        // Update status to submitted
        $purchaseRequest->status = PurchaseRequestStatus::submitted;
        $purchaseRequest->save();

        // Reload with relationships
        $purchaseRequest->load(['supplier', 'items', 'project']);

        return (new PurchaseRequestResource($purchaseRequest))->response();
    }

    /**
     * @OA\Post(
     *     path="/api/v1/purchase-requests/{purchaseRequest}/approve",
     *     summary="Aprovar requisição de compra",
     *     description="Aprova uma requisição de compra submetida, gerando automaticamente um Purchase Order",
     *     tags={"PurchaseRequests"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="purchaseRequest", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Requisição de compra aprovada com sucesso. Purchase Order gerado automaticamente.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", ref="#/components/schemas/PurchaseRequest"),
     *             @OA\Property(property="message", type="string", example="Requisição de compra aprovada. Purchase Order gerado automaticamente.")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Requisição de compra não encontrada"),
     *     @OA\Response(response=422, description="Requisição não pode ser aprovada")
     * )
     */
    public function approve(Request $request, PurchaseRequest $purchaseRequest): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        $this->checkBudgetPermission($user);
        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($purchaseRequest->project->company_id === $companyId, 403);

        // Check authorization using policy
        $this->authorize('approve', $purchaseRequest);

        // Update status to approved
        // The model's boot method will dispatch the ApprovedPurchaseRequest event
        // which triggers the GeneratePurchaseOrder job
        $purchaseRequest->status = PurchaseRequestStatus::approved;
        $purchaseRequest->save();

        // Reload with relationships (including the generated PO)
        $purchaseRequest->load(['supplier', 'items', 'project', 'purchaseOrder']);

        return (new PurchaseRequestResource($purchaseRequest))->response()->setData([
            'data' => new PurchaseRequestResource($purchaseRequest),
            'message' => 'Requisição de compra aprovada. Purchase Order gerado automaticamente.',
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/purchase-requests/{purchaseRequest}/reject",
     *     summary="Rejeitar requisição de compra",
     *     description="Rejeita uma requisição de compra submetida",
     *     tags={"PurchaseRequests"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="purchaseRequest", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="reason", type="string", nullable=true, example="Orçamento insuficiente", description="Motivo da rejeição")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Requisição de compra rejeitada com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", ref="#/components/schemas/PurchaseRequest")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Requisição de compra não encontrada"),
     *     @OA\Response(response=422, description="Requisição não pode ser rejeitada")
     * )
     */
    public function reject(Request $request, PurchaseRequest $purchaseRequest): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        $this->checkBudgetPermission($user);
        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($purchaseRequest->project->company_id === $companyId, 403);

        // Check authorization using policy
        $this->authorize('reject', $purchaseRequest);

        // Update status to rejected
        $purchaseRequest->status = PurchaseRequestStatus::rejected;

        // Add rejection reason to notes if provided
        if ($request->has('reason') && $request->input('reason')) {
            $existingNotes = $purchaseRequest->notes ?? '';
            $rejectionNote = "\n\n[Rejeitado em " . now()->format('d/m/Y H:i') . '] Motivo: ' . $request->input('reason');
            $purchaseRequest->notes = $existingNotes . $rejectionNote;
        }

        $purchaseRequest->save();

        // Reload with relationships
        $purchaseRequest->load(['supplier', 'items', 'project']);

        return (new PurchaseRequestResource($purchaseRequest))->response();
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/purchase-requests/{purchaseRequest}",
     *     summary="Remover requisição de compra",
     *     description="Remove uma requisição de compra (apenas se status for draft)",
     *     tags={"PurchaseRequests"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="purchaseRequest", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=204,
     *         description="Requisição de compra removida com sucesso"
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Requisição de compra não encontrada"),
     *     @OA\Response(response=422, description="Requisição não pode ser removida")
     * )
     */
    public function destroy(Request $request, PurchaseRequest $purchaseRequest): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        $this->checkBudgetPermission($user);
        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($purchaseRequest->project->company_id === $companyId, 403);
        abort_unless($user->projects()->whereKey($purchaseRequest->project_id)->exists(), 403);

        // Check if PR can be deleted
        if (! $purchaseRequest->canBeDeleted()) {
            return response()->json([
                'message' => 'Não é possível remover uma requisição de compra com status "' . $purchaseRequest->status->value . '".',
            ], 422);
        }

        $purchaseRequest->delete();

        return response()->json(null, 204);
    }
}

