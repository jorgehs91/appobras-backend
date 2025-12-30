<?php

namespace App\Http\Controllers;

use App\Http\Requests\Supplier\StoreSupplierRequest;
use App\Http\Requests\Supplier\UpdateSupplierRequest;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Suppliers",
 *     description="Gerenciamento de fornecedores"
 * )
 */
class SupplierController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/suppliers",
     *     summary="Listar fornecedores",
     *     description="Retorna lista de fornecedores",
     *     tags={"Suppliers"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="X-Company-Id",
     *         in="header",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de fornecedores",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Fornecedor ABC"),
     *                     @OA\Property(property="cnpj", type="string", example="12.345.678/0001-90"),
     *                     @OA\Property(property="contact", type="string", example="(11) 98765-4321"),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 )
     *             )
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

        $suppliers = Supplier::query()
            ->orderBy('name')
            ->get();

        return SupplierResource::collection($suppliers)->response();
    }

    /**
     * @OA\Get(
     *     path="/api/v1/suppliers/{supplier}",
     *     summary="Exibir fornecedor",
     *     description="Retorna detalhes de um fornecedor específico",
     *     tags={"Suppliers"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="X-Company-Id",
     *         in="header",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="supplier",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalhes do fornecedor",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Fornecedor não encontrado")
     * )
     */
    public function show(Request $request, Supplier $supplier): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);

        return (new SupplierResource($supplier))->response();
    }

    /**
     * @OA\Post(
     *     path="/api/v1/suppliers",
     *     summary="Criar fornecedor",
     *     description="Cria um novo fornecedor",
     *     tags={"Suppliers"},
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
     *             required={"name", "cnpj"},
     *             @OA\Property(property="name", type="string", example="Fornecedor ABC"),
     *             @OA\Property(property="cnpj", type="string", example="12.345.678/0001-90"),
     *             @OA\Property(property="contact", type="string", example="(11) 98765-4321")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Fornecedor criado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Fornecedor ABC"),
     *                 @OA\Property(property="cnpj", type="string", example="12.345.678/0001-90"),
     *                 @OA\Property(property="contact", type="string", example="(11) 98765-4321")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=422, description="Erro de validação")
     * )
     */
    public function store(StoreSupplierRequest $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);

        $payload = $request->validated();

        $supplier = Supplier::query()->create($payload);

        return (new SupplierResource($supplier))->response()->setStatusCode(201);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/suppliers/{supplier}",
     *     summary="Atualizar fornecedor",
     *     description="Atualiza informações de um fornecedor existente",
     *     tags={"Suppliers"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="X-Company-Id",
     *         in="header",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="supplier",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Fornecedor ABC"),
     *             @OA\Property(property="cnpj", type="string", example="12.345.678/0001-90"),
     *             @OA\Property(property="contact", type="string", example="(11) 98765-4321")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Fornecedor atualizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Fornecedor não encontrado"),
     *     @OA\Response(response=422, description="Erro de validação")
     * )
     */
    public function update(UpdateSupplierRequest $request, Supplier $supplier): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);

        $supplier->fill($request->validated());
        $supplier->save();

        return (new SupplierResource($supplier))->response();
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/suppliers/{supplier}",
     *     summary="Remover fornecedor",
     *     description="Remove um fornecedor (soft delete)",
     *     tags={"Suppliers"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="X-Company-Id",
     *         in="header",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="supplier",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Fornecedor removido com sucesso"
     *     ),
     *     @OA\Response(response=403, description="Sem permissão"),
     *     @OA\Response(response=404, description="Fornecedor não encontrado")
     * )
     */
    public function destroy(Request $request, Supplier $supplier): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);

        $supplier->delete();

        return response()->json(null, 204);
    }
}

