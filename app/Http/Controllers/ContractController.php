<?php

namespace App\Http\Controllers;

use App\Enums\ContractStatus;
use App\Http\Requests\Contract\StoreContractRequest;
use App\Http\Requests\Contract\UpdateContractRequest;
use App\Http\Resources\ContractResource;
use App\Models\Contract;
use App\Models\Contractor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Contracts",
 *     description="Gerenciamento de contratos de empreiteiros"
 * )
 */
class ContractController extends Controller
{
    /**
     * Verifica se o usuário tem permissão para acessar recursos de contratos.
     */
    protected function checkBudgetPermission($user): void
    {
        abort_unless(
            $user->hasBudgetAccess(),
            403,
            'Apenas usuários com role Financeiro ou Admin Obra podem acessar recursos de contratos.'
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/contractors/{contractor}/contracts",
     *     summary="Listar contratos do empreiteiro",
     *     description="Retorna todos os contratos de um empreiteiro",
     *     tags={"Contracts"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="contractor", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string", enum={"draft", "active", "completed", "canceled"}), description="Filtrar por status"),
     *     @OA\Parameter(name="project_id", in="query", required=false, @OA\Schema(type="integer"), description="Filtrar por projeto"),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de contratos",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Contract"))
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

        $query = Contract::query()
            ->where('contractor_id', $contractor->id)
            ->with(['contractor', 'project']);

        // Filter by status if provided
        if ($request->has('status')) {
            $status = ContractStatus::tryFrom($request->input('status'));
            if ($status) {
                $query->where('status', $status);
            }
        }

        // Filter by project if provided
        if ($request->has('project_id')) {
            $query->where('project_id', $request->input('project_id'));
        }

        $contracts = $query->orderByDesc('created_at')->get();

        return ContractResource::collection($contracts)->response();
    }

    /**
     * @OA\Post(
     *     path="/api/v1/contractors/{contractor}/contracts",
     *     summary="Criar contrato",
     *     description="Cria um novo contrato para o empreiteiro",
     *     tags={"Contracts"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="contractor", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"project_id", "value", "start_date", "status"},
     *             @OA\Property(property="project_id", type="integer", example=1),
     *             @OA\Property(property="value", type="number", format="float", example=50000.00),
     *             @OA\Property(property="start_date", type="string", format="date", example="2025-01-01"),
     *             @OA\Property(property="end_date", type="string", format="date", nullable=true, example="2025-12-31"),
     *             @OA\Property(property="status", type="string", enum={"draft", "active", "completed", "canceled"}, example="draft")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Contrato criado com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", ref="#/components/schemas/Contract")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Empreiteiro não encontrado"),
     *     @OA\Response(response=422, description="Erro de validação")
     * )
     */
    public function store(StoreContractRequest $request, Contractor $contractor): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        $this->checkBudgetPermission($user);
        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($contractor->company_id === $companyId, 403);

        // Verify project belongs to company
        $projectId = $request->input('project_id');
        $project = \App\Models\Project::find($projectId);
        abort_unless($project && $project->company_id === $companyId, 403, 'Projeto não pertence à empresa.');

        $payload = $request->validated();
        $payload['contractor_id'] = $contractor->id;
        $payload['created_by'] = $user->id;
        $payload['updated_by'] = $user->id;

        $contract = Contract::query()->create($payload);

        return (new ContractResource($contract->load(['contractor', 'project'])))->response()->setStatusCode(201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/contractors/{contractor}/contracts/{contract}",
     *     summary="Visualizar contrato",
     *     description="Retorna detalhes de um contrato específico",
     *     tags={"Contracts"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="contractor", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="contract", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Detalhes do contrato",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", ref="#/components/schemas/Contract")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Contrato não encontrado")
     * )
     */
    public function show(Request $request, Contractor $contractor, Contract $contract): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        $this->checkBudgetPermission($user);
        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($contractor->company_id === $companyId, 403);
        abort_unless($contract->contractor_id === $contractor->id, 404, 'Contrato não pertence ao empreiteiro.');

        return (new ContractResource($contract->load(['contractor', 'project', 'workOrders', 'payments'])))->response();
    }

    /**
     * @OA\Put(
     *     path="/api/v1/contractors/{contractor}/contracts/{contract}",
     *     summary="Atualizar contrato",
     *     description="Atualiza informações de um contrato existente",
     *     tags={"Contracts"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="contractor", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="contract", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="project_id", type="integer", example=1),
     *             @OA\Property(property="value", type="number", format="float", example=50000.00),
     *             @OA\Property(property="start_date", type="string", format="date", example="2025-01-01"),
     *             @OA\Property(property="end_date", type="string", format="date", nullable=true, example="2025-12-31"),
     *             @OA\Property(property="status", type="string", enum={"draft", "active", "completed", "canceled"}, example="active")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Contrato atualizado com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", ref="#/components/schemas/Contract")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Contrato não encontrado"),
     *     @OA\Response(response=422, description="Erro de validação")
     * )
     */
    public function update(UpdateContractRequest $request, Contractor $contractor, Contract $contract): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        $this->checkBudgetPermission($user);
        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($contractor->company_id === $companyId, 403);
        abort_unless($contract->contractor_id === $contractor->id, 404, 'Contrato não pertence ao empreiteiro.');

        // Verify project belongs to company if project_id is being updated
        if ($request->has('project_id')) {
            $projectId = $request->input('project_id');
            $project = \App\Models\Project::find($projectId);
            abort_unless($project && $project->company_id === $companyId, 403, 'Projeto não pertence à empresa.');
        }

        $payload = $request->validated();
        $contract->fill($payload);
        $contract->updated_by = $user->id;
        $contract->save();

        return (new ContractResource($contract->load(['contractor', 'project'])))->response();
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/contractors/{contractor}/contracts/{contract}",
     *     summary="Remover contrato",
     *     description="Remove um contrato (soft delete)",
     *     tags={"Contracts"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="contractor", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="contract", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=204,
     *         description="Contrato removido com sucesso"
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Contrato não encontrado")
     * )
     */
    public function destroy(Request $request, Contractor $contractor, Contract $contract): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        $this->checkBudgetPermission($user);
        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($contractor->company_id === $companyId, 403);
        abort_unless($contract->contractor_id === $contractor->id, 404, 'Contrato não pertence ao empreiteiro.');

        $contract->delete();

        return response()->json(null, 204);
    }
}

