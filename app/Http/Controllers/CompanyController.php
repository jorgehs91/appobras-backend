<?php

namespace App\Http\Controllers;

use App\Http\Resources\CompanyResource;
use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Role;

/**
 * @OA\Tag(
 *     name="Companies",
 *     description="Gerenciamento de empresas"
 * )
 */
class CompanyController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/companies",
     *     summary="Listar empresas do usuário",
     *     description="Retorna todas as empresas que o usuário autenticado pertence",
     *     tags={"Companies"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de empresas",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Construtora ABC"),
     *                     @OA\Property(property="created_at", type="string", format="date-time")
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
        $companies = $user->companies()->orderBy('name')->get();
        return CompanyResource::collection($companies)->response();
    }

    /**
     * @OA\Post(
     *     path="/api/v1/companies",
     *     summary="Criar empresa",
     *     description="Cria uma nova empresa e atribui o usuário como Admin",
     *     tags={"Companies"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", minLength=2, maxLength=255, example="Minha Construtora")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Empresa criada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Minha Construtora"),
     *                 @OA\Property(property="created_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=422, description="Erro de validação")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();

        $company = Company::query()->create([
            'name' => (string) $validated['name'],
        ]);

        $user->companies()->syncWithoutDetaching([$company->id]);
        // define contexto de equipe
        app(PermissionRegistrar::class)->setPermissionsTeamId($company->id);
        // Admin Obra
        $user->assignRole(Role::findByName(\App\Enums\SystemRole::AdminObra->value, 'sanctum'));

        // define company ativa
        $user->current_company_id = $company->id;
        $user->save();

        return (new CompanyResource($company))->response()->setStatusCode(201);
    }
}


