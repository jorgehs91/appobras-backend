<?php

namespace App\Http\Controllers;

use App\Http\Requests\Expense\StoreExpenseRequest;
use App\Http\Requests\Expense\UpdateExpenseRequest;
use App\Http\Resources\ExpenseResource;
use App\Models\Expense;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Tag(
 *     name="Expenses",
 *     description="Gerenciamento de despesas realizadas do projeto"
 * )
 */
class ExpenseController extends Controller
{
    /**
     * Verifica se o usuário tem permissão para acessar recursos de despesas.
     */
    protected function checkBudgetPermission($user): void
    {
        abort_unless(
            $user->hasBudgetAccess(),
            403,
            'Apenas usuários com role Financeiro ou Admin Obra podem acessar recursos de despesas.'
        );
    }

    /**
     * Retorna o disk configurado para armazenar comprovantes de despesas.
     * Por padrão usa 'local', mas pode ser alterado via env EXPENSE_RECEIPTS_DISK.
     * Para usar S3, defina EXPENSE_RECEIPTS_DISK=s3 no .env
     *
     * @return string
     */
    protected function getReceiptDisk(): string
    {
        return config('filesystems.expense_receipts_disk', 'local');
    }

    /**
     * @OA\Get(
     *     path="/api/v1/projects/{project}/expenses",
     *     summary="Listar despesas do projeto",
     *     description="Retorna todas as despesas de um projeto",
     *     tags={"Expenses"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="project", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string", enum={"draft", "approved"}), description="Filtrar por status"),
     *     @OA\Parameter(name="date_from", in="query", required=false, @OA\Schema(type="string", format="date"), description="Filtrar despesas a partir desta data"),
     *     @OA\Parameter(name="date_to", in="query", required=false, @OA\Schema(type="string", format="date"), description="Filtrar despesas até esta data"),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de despesas",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Expense"))
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

        $query = Expense::query()
            ->where('project_id', $project->id)
            ->with(['costItem', 'project']);

        // Filter by status if provided
        if ($request->has('status')) {
            $status = \App\Enums\ExpenseStatus::tryFrom($request->input('status'));
            if ($status) {
                $query->byStatus($status);
            }
        }

        // Filter by date range
        if ($request->has('date_from')) {
            $query->where('date', '>=', $request->input('date_from'));
        }
        if ($request->has('date_to')) {
            $query->where('date', '<=', $request->input('date_to'));
        }

        $expenses = $query->orderByDesc('date')->orderByDesc('created_at')->get();

        return ExpenseResource::collection($expenses)->response();
    }

    /**
     * @OA\Post(
     *     path="/api/v1/projects/{project}/expenses",
     *     summary="Criar despesa",
     *     description="Cria uma nova despesa no projeto com upload de comprovante (armazenado localmente por padrão, configurável via EXPENSE_RECEIPTS_DISK)",
     *     tags={"Expenses"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="project", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"amount", "date", "status"},
     *                 @OA\Property(property="cost_item_id", type="integer", nullable=true, example=1, description="ID do item de custo (opcional)"),
     *                 @OA\Property(property="amount", type="number", format="float", example=1500.00, description="Valor da despesa"),
     *                 @OA\Property(property="date", type="string", format="date", example="2025-12-29", description="Data da despesa"),
     *                 @OA\Property(property="description", type="string", nullable=true, example="Compra de materiais", description="Descrição da despesa"),
     *                 @OA\Property(property="receipt", type="string", format="binary", description="Comprovante (PDF, JPG, PNG - máx. 10MB). Obrigatório se status=approved"),
     *                 @OA\Property(property="status", type="string", enum={"draft", "approved"}, example="draft", description="Status da despesa")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Despesa criada com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", ref="#/components/schemas/Expense")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Projeto não encontrado"),
     *     @OA\Response(response=422, description="Erro de validação")
     * )
     */
    public function store(StoreExpenseRequest $request, Project $project): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        $this->checkBudgetPermission($user);
        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($project->company_id === $companyId, 403);
        abort_unless($user->projects()->whereKey($project->id)->exists(), 403);

        $payload = $request->validated();
        $payload['project_id'] = $project->id;
        $payload['created_by'] = $user->id;
        $payload['updated_by'] = $user->id;

        // Handle file upload if receipt is provided
        if ($request->hasFile('receipt')) {
            $file = $request->file('receipt');
            $disk = $this->getReceiptDisk();
            $path = $file->store("expenses/project-{$project->id}", $disk);
            $payload['receipt_path'] = $path;
        } elseif ($request->has('receipt_path')) {
            // Allow direct path input for testing or manual uploads
            $payload['receipt_path'] = $request->input('receipt_path');
        }

        $expense = Expense::query()->create($payload);

        return (new ExpenseResource($expense->load(['costItem', 'project'])))->response()->setStatusCode(201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/expenses/{expense}",
     *     summary="Visualizar despesa",
     *     description="Retorna detalhes de uma despesa específica",
     *     tags={"Expenses"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="expense", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Detalhes da despesa",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", ref="#/components/schemas/Expense")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Despesa não encontrada")
     * )
     */
    public function show(Request $request, Expense $expense): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        $this->checkBudgetPermission($user);
        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($expense->project->company_id === $companyId, 403);
        abort_unless($user->projects()->whereKey($expense->project_id)->exists(), 403);

        return (new ExpenseResource($expense->load(['costItem', 'project'])))->response();
    }

    /**
     * @OA\Put(
     *     path="/api/v1/expenses/{expense}",
     *     summary="Atualizar despesa",
     *     description="Atualiza informações de uma despesa existente",
     *     tags={"Expenses"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="expense", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="cost_item_id", type="integer", nullable=true),
     *                 @OA\Property(property="amount", type="number", format="float"),
     *                 @OA\Property(property="date", type="string", format="date"),
     *                 @OA\Property(property="description", type="string", nullable=true),
     *                 @OA\Property(property="receipt", type="string", format="binary", description="Novo comprovante (PDF, JPG, PNG - máx. 10MB)"),
     *                 @OA\Property(property="status", type="string", enum={"draft", "approved"})
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Despesa atualizada com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", ref="#/components/schemas/Expense")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Despesa não encontrada"),
     *     @OA\Response(response=422, description="Erro de validação")
     * )
     */
    public function update(UpdateExpenseRequest $request, Expense $expense): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        $this->checkBudgetPermission($user);
        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($expense->project->company_id === $companyId, 403);
        abort_unless($user->projects()->whereKey($expense->project_id)->exists(), 403);

        $payload = $request->validated();

        // Handle file upload if new receipt is provided
        if ($request->hasFile('receipt')) {
            $disk = $this->getReceiptDisk();
            // Delete old receipt if exists
            if ($expense->receipt_path && Storage::disk($disk)->exists($expense->receipt_path)) {
                Storage::disk($disk)->delete($expense->receipt_path);
            }

            $file = $request->file('receipt');
            $path = $file->store("expenses/project-{$expense->project_id}", $disk);
            $payload['receipt_path'] = $path;
        } elseif ($request->has('receipt_path')) {
            // Allow direct path input
            $payload['receipt_path'] = $request->input('receipt_path');
        }

        $expense->fill($payload);
        $expense->updated_by = $user->id;
        $expense->save();

        return (new ExpenseResource($expense->load(['costItem', 'project'])))->response();
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/expenses/{expense}",
     *     summary="Remover despesa",
     *     description="Remove uma despesa (soft delete) e exclui o comprovante do storage (local ou S3 conforme configuração)",
     *     tags={"Expenses"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="expense", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=204,
     *         description="Despesa removida com sucesso"
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Despesa não encontrada")
     * )
     */
    public function destroy(Request $request, Expense $expense): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        $this->checkBudgetPermission($user);
        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($expense->project->company_id === $companyId, 403);
        abort_unless($user->projects()->whereKey($expense->project_id)->exists(), 403);

        // Delete receipt file if exists
        $disk = $this->getReceiptDisk();
        if ($expense->receipt_path && Storage::disk($disk)->exists($expense->receipt_path)) {
            Storage::disk($disk)->delete($expense->receipt_path);
        }

        $expense->delete();

        return response()->json(null, 204);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/expenses/{expense}/receipt",
     *     summary="Baixar comprovante da despesa",
     *     description="Retorna o arquivo do comprovante para download",
     *     tags={"Expenses"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="expense", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Arquivo do comprovante",
     *         @OA\MediaType(
     *             mediaType="application/octet-stream",
     *             @OA\Schema(type="string", format="binary")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Despesa ou arquivo não encontrado")
     * )
     */
    public function downloadReceipt(Request $request, Expense $expense): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        $this->checkBudgetPermission($user);
        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($expense->project->company_id === $companyId, 403);
        abort_unless($user->projects()->whereKey($expense->project_id)->exists(), 403);
        abort_unless($expense->receipt_path, 404, 'Despesa não possui comprovante');

        $disk = $this->getReceiptDisk();
        abort_unless(Storage::disk($disk)->exists($expense->receipt_path), 404, 'Arquivo não encontrado no storage');

        return Storage::disk($disk)->response($expense->receipt_path);
    }
}

