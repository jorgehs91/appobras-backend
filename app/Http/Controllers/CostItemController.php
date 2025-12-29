<?php

namespace App\Http\Controllers;

use App\Http\Requests\CostItem\StoreCostItemRequest;
use App\Http\Requests\CostItem\UpdateCostItemRequest;
use App\Http\Resources\CostItemResource;
use App\Models\Budget;
use App\Models\CostItem;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Cost Items",
 *     description="Gerenciamento de itens de custo do orçamento"
 * )
 */
class CostItemController extends Controller
{
    /**
     * Verifica se o usuário tem permissão para acessar recursos de orçamento.
     */
    protected function checkBudgetPermission($user): void
    {
        abort_unless(
            $user->hasBudgetAccess(),
            403,
            'Apenas usuários com role Financeiro ou Admin Obra podem acessar recursos de orçamento.'
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/projects/{project}/budgets/{budget}/cost-items",
     *     summary="Listar itens de custo do orçamento",
     *     description="Retorna todos os itens de custo de um orçamento",
     *     tags={"Cost Items"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="project", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="budget", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="category", in="query", required=false, @OA\Schema(type="string"), description="Filtrar por categoria"),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de itens de custo",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/CostItem"))
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Orçamento não encontrado")
     * )
     */
    public function index(Request $request, Project $project, Budget $budget): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        $this->checkBudgetPermission($user);
        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($project->company_id === $companyId, 403);
        abort_unless($user->projects()->whereKey($project->id)->exists(), 403);
        abort_unless($budget->project_id === $project->id, 403);

        $query = CostItem::query()
            ->where('budget_id', $budget->id)
            ->with('budget');

        // Filter by category if provided
        if ($request->has('category')) {
            $query->where('category', $request->input('category'));
        }

        $costItems = $query->orderBy('category')->orderBy('name')->get();

        return CostItemResource::collection($costItems)->response();
    }

    /**
     * @OA\Post(
     *     path="/api/v1/projects/{project}/budgets/{budget}/cost-items",
     *     summary="Criar item de custo",
     *     description="Cria um novo item de custo no orçamento",
     *     tags={"Cost Items"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="project", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="budget", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "category", "planned_amount"},
     *             @OA\Property(property="name", type="string", example="Cimento"),
     *             @OA\Property(property="category", type="string", example="Materiais"),
     *             @OA\Property(property="planned_amount", type="number", format="float", example=5000.00),
     *             @OA\Property(property="unit", type="string", example="kg", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Item de custo criado com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", ref="#/components/schemas/CostItem")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Orçamento não encontrado"),
     *     @OA\Response(response=422, description="Erro de validação")
     * )
     */
    public function store(StoreCostItemRequest $request, Project $project, Budget $budget): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        $this->checkBudgetPermission($user);
        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($project->company_id === $companyId, 403);
        abort_unless($user->projects()->whereKey($project->id)->exists(), 403);
        abort_unless($budget->project_id === $project->id, 403);

        $payload = $request->validated();
        $payload['budget_id'] = $budget->id;
        $payload['created_by'] = $user->id;
        $payload['updated_by'] = $user->id;

        $costItem = CostItem::query()->create($payload);

        return (new CostItemResource($costItem->load('budget')))->response()->setStatusCode(201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/cost-items/{costItem}",
     *     summary="Visualizar item de custo",
     *     description="Retorna detalhes de um item de custo específico",
     *     tags={"Cost Items"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="costItem", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Detalhes do item de custo",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", ref="#/components/schemas/CostItem")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Item de custo não encontrado")
     * )
     */
    public function show(Request $request, CostItem $costItem): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        $this->checkBudgetPermission($user);
        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($costItem->budget->company_id === $companyId, 403);
        abort_unless($user->projects()->whereKey($costItem->budget->project_id)->exists(), 403);

        return (new CostItemResource($costItem->load('budget')))->response();
    }

    /**
     * @OA\Put(
     *     path="/api/v1/cost-items/{costItem}",
     *     summary="Atualizar item de custo",
     *     description="Atualiza informações de um item de custo existente",
     *     tags={"Cost Items"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="costItem", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Cimento"),
     *             @OA\Property(property="category", type="string", example="Materiais"),
     *             @OA\Property(property="planned_amount", type="number", format="float", example=6000.00),
     *             @OA\Property(property="unit", type="string", example="kg", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item de custo atualizado com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", ref="#/components/schemas/CostItem")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Item de custo não encontrado"),
     *     @OA\Response(response=422, description="Erro de validação")
     * )
     */
    public function update(UpdateCostItemRequest $request, CostItem $costItem): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        $this->checkBudgetPermission($user);
        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($costItem->budget->company_id === $companyId, 403);
        abort_unless($user->projects()->whereKey($costItem->budget->project_id)->exists(), 403);

        $costItem->fill($request->validated());
        $costItem->updated_by = $user->id;
        $costItem->save();

        return (new CostItemResource($costItem->load('budget')))->response();
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/cost-items/{costItem}",
     *     summary="Remover item de custo",
     *     description="Remove um item de custo (soft delete)",
     *     tags={"Cost Items"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="costItem", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=204,
     *         description="Item de custo removido com sucesso"
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Item de custo não encontrado")
     * )
     */
    public function destroy(Request $request, CostItem $costItem): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        $this->checkBudgetPermission($user);
        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($costItem->budget->company_id === $companyId, 403);
        abort_unless($user->projects()->whereKey($costItem->budget->project_id)->exists(), 403);

        $costItem->delete();

        return response()->json(null, 204);
    }
}