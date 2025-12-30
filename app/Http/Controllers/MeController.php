<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Services\ExpoPushService;
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

    /**
     * @OA\Put(
     *     path="/api/v1/user/preferences",
     *     summary="Atualizar preferências do usuário",
     *     description="Atualiza as preferências de notificação por email do usuário autenticado",
     *     tags={"Me"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email_notifications_enabled", type="boolean", example=true, description="Habilitar ou desabilitar notificações por email")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Preferências atualizadas com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/UserResource")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Erro de validação")
     * )
     */
    public function updatePreferences(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email_notifications_enabled' => ['sometimes', 'boolean'],
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();

        if (isset($validated['email_notifications_enabled'])) {
            $user->email_notifications_enabled = $validated['email_notifications_enabled'];
            $user->save();
        }

        return response()->json([
            'data' => new \App\Http\Resources\UserResource($user),
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/user/expo-token",
     *     summary="Registrar ou atualizar token Expo Push",
     *     description="Registra ou atualiza o token Expo Push do usuário autenticado para receber notificações push",
     *     tags={"Me"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"expo_push_token"},
     *             @OA\Property(property="expo_push_token", type="string", example="ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]", description="Token Expo Push do dispositivo")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Token registrado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Expo push token updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/UserResource")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Erro de validação - token inválido"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function updateExpoToken(Request $request, ExpoPushService $expoPushService): JsonResponse
    {
        $validated = $request->validate([
            'expo_push_token' => ['required', 'string', 'max:255'],
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();

        // Validar formato do token Expo
        if (! $expoPushService->isValidToken($validated['expo_push_token'])) {
            return response()->json([
                'message' => 'Invalid Expo push token format',
                'errors' => [
                    'expo_push_token' => ['The token must be a valid Expo push token format (ExponentPushToken[...] or ExpoPushToken[...])'],
                ],
            ], 422);
        }

        $user->expo_push_token = $validated['expo_push_token'];
        $user->save();

        return response()->json([
            'message' => 'Expo push token updated successfully',
            'data' => new \App\Http\Resources\UserResource($user),
        ], 200);
    }
}


