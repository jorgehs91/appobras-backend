<?php

namespace App\Http\Controllers;

use App\Http\Requests\License\StoreLicenseRequest;
use App\Http\Requests\License\UpdateLicenseRequest;
use App\Http\Resources\LicenseResource;
use App\Models\License;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Licenças
 *
 * Endpoints relacionados ao gerenciamento de licenças de projetos.
 *
 * @OA\Tag(
 *     name="Licenses",
 *     description="Gerenciamento de licenças de projetos"
 * )
 */
class LicenseController extends Controller
{
    /**
     * Verifica se o usuário tem permissão para acessar recursos de licenças.
     */
    protected function checkLicensePermission($user): void
    {
        abort_unless(
            $user->hasBudgetAccess(),
            403,
            'Apenas usuários com role Financeiro ou Admin Obra podem gerenciar licenças.'
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/licenses",
     *     summary="Listar licenças",
     *     description="Retorna todas as licenças com filtros opcionais",
     *     tags={"Licenses"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="project_id", in="query", required=false, @OA\Schema(type="integer"), description="Filtrar por projeto"),
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string"), description="Filtrar por status"),
     *     @OA\Parameter(name="expiring_soon", in="query", required=false, @OA\Schema(type="boolean"), description="Filtrar licenças próximas do vencimento"),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de licenças",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/License"))
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

        $this->checkLicensePermission($user);
        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);

        $query = License::query()
            ->whereHas('project', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })
            ->with(['file', 'project', 'creator', 'updater']);

        // Filter by project if provided
        if ($request->has('project_id')) {
            $projectId = (int) $request->input('project_id');
            // Verify project belongs to company
            $project = \App\Models\Project::find($projectId);
            abort_unless($project && $project->company_id === $companyId, 403, 'Projeto não pertence à empresa.');
            $query->where('project_id', $projectId);
        }

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by expiring soon if provided
        if ($request->boolean('expiring_soon')) {
            $days = (int) config('app.alert_license_days', env('ALERT_LICENSE_DAYS', 30));
            $query->expiringSoon($days);
        }

        $licenses = $query->orderBy('expiry_date')->get();

        return LicenseResource::collection($licenses)->response();
    }

    /**
     * @OA\Get(
     *     path="/api/v1/licenses/expiring",
     *     summary="Listar licenças próximas do vencimento",
     *     description="Retorna licenças que estão próximas do vencimento dentro do threshold configurado",
     *     tags={"Licenses"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="project_id", in="query", required=false, @OA\Schema(type="integer"), description="Filtrar por projeto"),
     *     @OA\Parameter(name="days", in="query", required=false, @OA\Schema(type="integer"), description="Número de dias para considerar como próximo do vencimento (padrão: 30)"),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de licenças próximas do vencimento",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/License"))
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão")
     * )
     */
    public function expiring(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        $this->checkLicensePermission($user);
        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);

        $days = (int) $request->input('days', config('app.alert_license_days', env('ALERT_LICENSE_DAYS', 30)));

        $query = License::query()
            ->whereHas('project', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })
            ->expiringSoon($days)
            ->with(['file', 'project', 'creator', 'updater']);

        // Filter by project if provided
        if ($request->has('project_id')) {
            $projectId = (int) $request->input('project_id');
            // Verify project belongs to company
            $project = \App\Models\Project::find($projectId);
            abort_unless($project && $project->company_id === $companyId, 403, 'Projeto não pertence à empresa.');
            $query->where('project_id', $projectId);
        }

        $licenses = $query->orderBy('expiry_date')->get();

        return LicenseResource::collection($licenses)->response();
    }

    /**
     * @OA\Post(
     *     path="/api/v1/licenses",
     *     summary="Criar licença",
     *     description="Cria uma nova licença",
     *     tags={"Licenses"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"file_id", "project_id", "expiry_date"},
     *             @OA\Property(property="file_id", type="integer", example=1),
     *             @OA\Property(property="project_id", type="integer", example=1),
     *             @OA\Property(property="expiry_date", type="string", format="date", example="2025-12-31"),
     *             @OA\Property(property="status", type="string", nullable=true, example="active"),
     *             @OA\Property(property="notes", type="string", nullable=true, example="Licença de alvará de construção")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Licença criada com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", ref="#/components/schemas/License")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=422, description="Erro de validação")
     * )
     */
    public function store(StoreLicenseRequest $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        $this->checkLicensePermission($user);
        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);

        // Verify project belongs to company
        $projectId = $request->input('project_id');
        $project = \App\Models\Project::find($projectId);
        abort_unless($project && $project->company_id === $companyId, 403, 'Projeto não pertence à empresa.');

        // Verify file exists
        $fileId = $request->input('file_id');
        $file = \App\Models\File::find($fileId);
        abort_unless($file, 404, 'Arquivo não encontrado.');

        $payload = $request->validated();
        $payload['created_by'] = $user->id;
        $payload['updated_by'] = $user->id;

        $license = License::query()->create($payload);

        return (new LicenseResource($license->load(['file', 'project', 'creator', 'updater'])))->response()->setStatusCode(201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/licenses/{license}",
     *     summary="Visualizar licença",
     *     description="Retorna detalhes de uma licença específica",
     *     tags={"Licenses"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="license", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Detalhes da licença",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", ref="#/components/schemas/License")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Licença não encontrada")
     * )
     */
    public function show(Request $request, License $license): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        $this->checkLicensePermission($user);
        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($license->project->company_id === $companyId, 403, 'Licença não pertence à empresa.');

        return (new LicenseResource($license->load(['file', 'project', 'creator', 'updater'])))->response();
    }

    /**
     * @OA\Put(
     *     path="/api/v1/licenses/{license}",
     *     summary="Atualizar licença",
     *     description="Atualiza informações de uma licença existente",
     *     tags={"Licenses"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="license", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="file_id", type="integer", example=1),
     *             @OA\Property(property="project_id", type="integer", example=1),
     *             @OA\Property(property="expiry_date", type="string", format="date", example="2025-12-31"),
     *             @OA\Property(property="status", type="string", nullable=true, example="active"),
     *             @OA\Property(property="notes", type="string", nullable=true, example="Licença renovada")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Licença atualizada com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", ref="#/components/schemas/License")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Licença não encontrada"),
     *     @OA\Response(response=422, description="Erro de validação")
     * )
     */
    public function update(UpdateLicenseRequest $request, License $license): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        $this->checkLicensePermission($user);
        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($license->project->company_id === $companyId, 403, 'Licença não pertence à empresa.');

        // Verify project belongs to company if project_id is being updated
        if ($request->has('project_id')) {
            $projectId = $request->input('project_id');
            $project = \App\Models\Project::find($projectId);
            abort_unless($project && $project->company_id === $companyId, 403, 'Projeto não pertence à empresa.');
        }

        // Verify file exists if file_id is being updated
        if ($request->has('file_id')) {
            $fileId = $request->input('file_id');
            $file = \App\Models\File::find($fileId);
            abort_unless($file, 404, 'Arquivo não encontrado.');
        }

        $payload = $request->validated();
        $license->fill($payload);
        $license->updated_by = $user->id;
        $license->save();

        return (new LicenseResource($license->load(['file', 'project', 'creator', 'updater'])))->response();
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/licenses/{license}",
     *     summary="Remover licença",
     *     description="Remove uma licença (soft delete)",
     *     tags={"Licenses"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="X-Company-Id", in="header", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="license", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=204,
     *         description="Licença removida com sucesso"
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Licença não encontrada")
     * )
     */
    public function destroy(Request $request, License $license): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        $this->checkLicensePermission($user);
        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($license->project->company_id === $companyId, 403, 'Licença não pertence à empresa.');

        $license->delete();

        return response()->json(null, 204);
    }
}

