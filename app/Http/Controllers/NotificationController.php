<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Notifications",
 *     description="Gerenciamento de notificações do usuário"
 * )
 */
class NotificationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/notifications",
     *     summary="Listar notificações do usuário",
     *     description="Retorna as notificações do usuário autenticado com paginação e contador de não lidas",
     *     tags={"Notifications"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="read",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="boolean"),
     *         description="Filtrar por lidas (true) ou não lidas (false)"
     *     ),
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Filtrar por tipo de notificação"
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", default=15, maximum=100),
     *         description="Número de itens por página"
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", default=1),
     *         description="Número da página"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de notificações",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Notification")),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="unread_count", type="integer", example=5, description="Total de notificações não lidas"),
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=42),
     *                 @OA\Property(property="last_page", type="integer", example=3)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $query = $user->userNotifications();

        // Filtrar por status de leitura
        if ($request->has('read')) {
            $isRead = filter_var($request->input('read'), FILTER_VALIDATE_BOOLEAN);
            if ($isRead) {
                $query->read();
            } else {
                $query->unread();
            }
        }

        // Filtrar por tipo
        if ($request->has('type')) {
            $query->byType($request->input('type'));
        }

        $perPage = min((int) $request->input('per_page', 15), 100); // Máximo 100 por página

        $notifications = $query->orderByDesc('created_at')
            ->paginate($perPage);

        // Contar notificações não lidas
        $unreadCount = $user->unreadUserNotifications()->count();

        return response()->json([
            'data' => NotificationResource::collection($notifications->items()),
            'meta' => [
                'unread_count' => $unreadCount,
                'current_page' => $notifications->currentPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
                'last_page' => $notifications->lastPage(),
            ],
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/notifications/{id}/read",
     *     summary="Marcar notificação como lida",
     *     description="Marca uma notificação específica como lida para o usuário autenticado",
     *     tags={"Notifications"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID da notificação"
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Notificação marcada como lida com sucesso"
     *     ),
     *     @OA\Response(response=401, description="Não autenticado"),
     *     @OA\Response(response=403, description="Notificação não pertence ao usuário"),
     *     @OA\Response(response=404, description="Notificação não encontrada")
     * )
     */
    public function markAsRead(Request $request, int $id): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $notification = Notification::query()
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $notification->markAsRead();

        return response()->json(null, 204);
    }
}

