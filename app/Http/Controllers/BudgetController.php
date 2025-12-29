<?php

namespace App\Http\Controllers;

use App\Http\Requests\Budget\StoreBudgetRequest;
use App\Http\Requests\Budget\UpdateBudgetRequest;
use App\Http\Resources\BudgetResource;
use App\Models\Budget;
use App\Models\CostItem;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(
 *     name="Budgets",
 *     description="Gerenciamento de orçamentos do projeto"
 * )
 */
class BudgetController extends Controller
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
     *     path="/api/v1/projects/{project}/budgets",
     *     summary="Listar orçamentos do projeto",
     *     description="Retorna todos os orçamentos de um projeto",
     *     tags={"Budgets"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="project", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de orçamentos",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Budget"))
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão")
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

        $budgets = Budget::query()
            ->where('project_id', $project->id)
            ->with('costItems')
            ->orderBy('created_at', 'desc')
            ->get();

        return BudgetResource::collection($budgets)->response();
    }

    /**
     * @OA\Post(
     *     path="/api/v1/projects/{project}/budgets",
     *     summary="Criar orçamento",
     *     description="Cria um novo orçamento no projeto",
     *     tags={"Budgets"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="project", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"total_planned"},
     *             @OA\Property(property="total_planned", type="number", format="float", example=100000.00)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Orçamento criado com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", ref="#/components/schemas/Budget")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=422, description="Erro de validação")
     * )
     */
    public function store(StoreBudgetRequest $request, Project $project): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        $this->checkBudgetPermission($user);
        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($project->company_id === $companyId, 403);
        abort_unless($user->projects()->whereKey($project->id)->exists(), 403);

        $payload = $request->validated();
        $payload['company_id'] = $companyId;
        $payload['project_id'] = $project->id;
        $payload['created_by'] = $user->id;
        $payload['updated_by'] = $user->id;

        $budget = Budget::query()->create($payload);

        return (new BudgetResource($budget->load('costItems')))->response()->setStatusCode(201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/budgets/{budget}",
     *     summary="Visualizar orçamento",
     *     description="Retorna detalhes de um orçamento específico",
     *     tags={"Budgets"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="budget", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Detalhes do orçamento",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", ref="#/components/schemas/Budget")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Orçamento não encontrado")
     * )
     */
    public function show(Request $request, Budget $budget): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        $this->checkBudgetPermission($user);
        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($budget->company_id === $companyId, 403);
        abort_unless($user->projects()->whereKey($budget->project_id)->exists(), 403);

        return (new BudgetResource($budget->load('costItems')))->response();
    }

    /**
     * @OA\Put(
     *     path="/api/v1/budgets/{budget}",
     *     summary="Atualizar orçamento",
     *     description="Atualiza informações de um orçamento existente",
     *     tags={"Budgets"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="budget", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="total_planned", type="number", format="float", example=150000.00)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Orçamento atualizado com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", ref="#/components/schemas/Budget")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Orçamento não encontrado"),
     *     @OA\Response(response=422, description="Erro de validação")
     * )
     */
    public function update(UpdateBudgetRequest $request, Budget $budget): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        $this->checkBudgetPermission($user);
        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($budget->company_id === $companyId, 403);
        abort_unless($user->projects()->whereKey($budget->project_id)->exists(), 403);

        $budget->fill($request->validated());
        $budget->updated_by = $user->id;
        $budget->save();

        return (new BudgetResource($budget->load('costItems')))->response();
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/budgets/{budget}",
     *     summary="Remover orçamento",
     *     description="Remove um orçamento (soft delete). Não é possível remover orçamentos que contêm itens de custo",
     *     tags={"Budgets"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="budget", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=204,
     *         description="Orçamento removido com sucesso"
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Orçamento não encontrado"),
     *     @OA\Response(
     *         response=422,
     *         description="Não é possível deletar um orçamento que contém itens de custo",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Não é possível deletar um orçamento que contém itens de custo.")
     *         )
     *     )
     * )
     */
    public function destroy(Request $request, Budget $budget): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        $this->checkBudgetPermission($user);
        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($budget->company_id === $companyId, 403);
        abort_unless($user->projects()->whereKey($budget->project_id)->exists(), 403);

        // Check if budget has cost items
        if ($budget->costItems()->exists()) {
            return response()->json([
                'message' => 'Não é possível deletar um orçamento que contém itens de custo.',
            ], 422);
        }

        $budget->delete();

        return response()->json(null, 204);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/projects/{project}/budget/summary",
     *     summary="Resumo do orçamento por categoria",
     *     description="Retorna o total planejado agrupado por categoria de itens de custo",
     *     tags={"Budgets"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="project", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Resumo do orçamento por categoria",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="categories", type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="category", type="string", example="Materiais"),
     *                         @OA\Property(property="total_planned", type="number", format="float", example=50000.00)
     *                     )
     *                 ),
     *                 @OA\Property(property="total", type="number", format="float", example=100000.00)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Projeto não encontrado")
     * )
     */
    public function summary(Request $request, Project $project): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        $this->checkBudgetPermission($user);
        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($project->company_id === $companyId, 403);
        abort_unless($user->projects()->whereKey($project->id)->exists(), 403);

        // Get budget for the project (assuming one budget per project)
        $budget = Budget::query()
            ->where('project_id', $project->id)
            ->first();

        if (!$budget) {
            return response()->json([
                'data' => [
                    'categories' => [],
                    'total' => 0,
                ],
            ]);
        }

        // Aggregate by category using Eloquent to respect soft deletes
        $categories = CostItem::query()
            ->where('budget_id', $budget->id)
            ->select('category', DB::raw('SUM(planned_amount) as total_planned'))
            ->groupBy('category')
            ->orderBy('category')
            ->get()
            ->map(function ($item) {
                return [
                    'category' => $item->category,
                    'total_planned' => (float) $item->total_planned,
                ];
            });

        $total = (float) CostItem::query()
            ->where('budget_id', $budget->id)
            ->sum('planned_amount');

        return response()->json([
            'data' => [
                'categories' => $categories,
                'total' => $total,
            ],
        ]);
    }
}