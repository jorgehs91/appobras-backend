<?php

namespace App\Http\Controllers;

use App\Http\Requests\Contractor\StoreContractorRequest;
use App\Http\Requests\Contractor\UpdateContractorRequest;
use App\Http\Resources\ContractorResource;
use App\Models\Contractor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Contractors",
 *     description="Gerenciamento de empreiteiros"
 * )
 */
class ContractorController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/contractors",
     *     summary="Listar empreiteiros",
     *     description="Retorna lista de empreiteiros da empresa",
     *     tags={"Contractors"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="X-Company-Id",
     *         in="header",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de empreiteiros",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Construtora ABC"),
     *                     @OA\Property(property="contact", type="string", example="(11) 98765-4321"),
     *                     @OA\Property(property="specialties", type="string", example="Fundação, Estrutura")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);

        $contractors = Contractor::query()
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get();

        return ContractorResource::collection($contractors)->response();
    }

    /**
     * @OA\Post(
     *     path="/api/v1/contractors",
     *     summary="Criar empreiteiro",
     *     description="Cria um novo empreiteiro na empresa",
     *     tags={"Contractors"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="X-Company-Id",
     *         in="header",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Construtora ABC"),
     *             @OA\Property(property="contact", type="string", example="(11) 98765-4321"),
     *             @OA\Property(property="specialties", type="string", example="Fundação, Estrutura")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Empreiteiro criado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Construtora ABC"),
     *                 @OA\Property(property="contact", type="string", example="(11) 98765-4321"),
     *                 @OA\Property(property="specialties", type="string", example="Fundação, Estrutura")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=422, description="Erro de validação")
     * )
     */
    public function store(StoreContractorRequest $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);

        $payload = $request->validated();
        $payload['company_id'] = $companyId;

        $contractor = Contractor::query()->create($payload);

        return (new ContractorResource($contractor))->response()->setStatusCode(201);
    }

    /**
     * Update the specified contractor.
     */
    public function update(UpdateContractorRequest $request, Contractor $contractor): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($contractor->company_id === $companyId, 403);

        $contractor->fill($request->validated());
        $contractor->save();

        return (new ContractorResource($contractor))->response();
    }

    /**
     * Remove the specified contractor (soft delete).
     */
    public function destroy(Request $request, Contractor $contractor): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);
        abort_unless($contractor->company_id === $companyId, 403);

        $contractor->delete();

        return response()->json(null, 204);
    }
}

