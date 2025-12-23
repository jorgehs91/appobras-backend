<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Me",
 *     description="Operações do usuário autenticado"
 * )
 */
class MeController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/me/switch-company",
     *     summary="Trocar empresa ativa",
     *     description="Altera a empresa ativa do usuário autenticado",
     *     tags={"Me"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"company_id"},
     *             @OA\Property(property="company_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Empresa trocada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Company switched")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Usuário não pertence à empresa"),
     *     @OA\Response(response=422, description="Erro de validação")
     * )
     */
    public function switchCompany(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_id' => ['required', 'integer', 'exists:companies,id'],
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();

        abort_unless($user->companies()->whereKey($validated['company_id'])->exists(), 403);

        $user->current_company_id = (int) $validated['company_id'];
        $user->save();

        return response()->json(['message' => 'Company switched'], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/me/switch-project",
     *     summary="Trocar projeto ativo",
     *     description="Altera o projeto ativo do usuário autenticado dentro da empresa atual",
     *     tags={"Me"},
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
     *             required={"project_id"},
     *             @OA\Property(property="project_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Projeto trocado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Project switched")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Usuário não é membro do projeto"),
     *     @OA\Response(response=404, description="Projeto não encontrado"),
     *     @OA\Response(response=422, description="Erro de validação")
     * )
     */
    public function switchProject(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'project_id' => ['required', 'integer', 'exists:projects,id'],
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();
        $companyId = (int) $request->header('X-Company-Id');

        abort_unless($companyId && $user->companies()->whereKey($companyId)->exists(), 403);

        $project = \App\Models\Project::query()
            ->whereKey($validated['project_id'])
            ->where('company_id', $companyId)
            ->firstOrFail();

        // Requer que o usuário seja membro do projeto
        abort_unless($user->projects()->whereKey($project->id)->exists(), 403);

        $user->current_project_id = (int) $project->id;
        $user->save();

        return response()->json(['message' => 'Project switched'], 200);
    }
}


