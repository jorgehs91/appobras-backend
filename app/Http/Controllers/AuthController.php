<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

/**
 * @group Autenticação
 *
 * Endpoints relacionados à autenticação de usuários (login, registro, recuperação de senha).
 */
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *   path="/api/v1/auth/register",
     *   tags={"Auth"},
     *   summary="Register account",
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\JsonContent(
     *       required={"name","email","password","password_confirmation"},
     *
     *       @OA\Property(property="name", type="string"),
     *       @OA\Property(property="email", type="string", format="email"),
     *       @OA\Property(property="password", type="string", format="password"),
     *       @OA\Property(property="password_confirmation", type="string", format="password"),
     *       @OA\Property(property="device_name", type="string")
     *     )
     *   ),
     *
     *   @OA\Response(response=201, description="Created")
     * )
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = \App\Models\User::query()->create([
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'password' => $request->string('password')->toString(),
        ]);

        $deviceName = $request->string('device_name')->toString() ?: 'api';
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ], 201);
    }

    /**
     * @OA\Post(
     *   path="/api/v1/auth/login",
     *   tags={"Auth"},
     *   summary="Login",
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\JsonContent(
     *       required={"email","password"},
     *
     *       @OA\Property(property="email", type="string", format="email"),
     *       @OA\Property(property="password", type="string", format="password"),
     *       @OA\Property(property="device_name", type="string")
     *     )
     *   ),
     *
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only(['email', 'password']);

        if (! Auth::guard('web')->attempt($credentials)) {
            // Incrementar tentativa falha se usuário existir
            $user = \App\Models\User::query()->where('email', $request->string('email'))
                ->first();
            if ($user) {
                $user->increment('failed_attempts');
            }
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        /** @var \App\Models\User $user */
        $user = Auth::guard('web')->user();

        $user->forceFill([
            'last_login_at' => now(),
            'failed_attempts' => 0,
        ])->save();

        $deviceName = $request->string('device_name')->toString() ?: 'api';
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ], 200);
    }

    /**
     * @OA\Post(
     *   path="/api/v1/auth/logout",
     *   tags={"Auth"},
     *   summary="Logout",
     *   security={{"sanctum":{}}},
     *
     *   @OA\Response(response=204, description="No Content")
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $token = $user?->currentAccessToken();
        if ($token) {
            $token->delete();
        }

        return response()->json([], 204);
    }

    /**
     * @OA\Post(
     *   path="/api/v1/auth/refresh",
     *   tags={"Auth"},
     *   summary="Refresh token",
     *   security={{"sanctum":{}}},
     *
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function refresh(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $currentToken = $user->currentAccessToken();
        if ($currentToken) {
            $currentToken->delete();
        }

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ], 200);
    }

    /**
     * @OA\Post(
     *   path="/api/v1/auth/forgot",
     *   tags={"Auth"},
     *   summary="Request password reset",
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\JsonContent(
     *       required={"email"},
     *
     *       @OA\Property(property="email", type="string", format="email")
     *     )
     *   ),
     *
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function forgot(ForgotPasswordRequest $request): JsonResponse
    {
        $status = Password::sendResetLink(
            $request->only('email')
        );

        // Não revelar se o e-mail existe (mitiga enumeração)
        return response()->json([
            'message' => __($status),
        ], 200);
    }

    /**
     * @OA\Post(
     *   path="/api/v1/auth/reset",
     *   tags={"Auth"},
     *   summary="Reset password",
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\JsonContent(
     *       required={"email","token","password","password_confirmation"},
     *
     *       @OA\Property(property="email", type="string", format="email"),
     *       @OA\Property(property="token", type="string"),
     *       @OA\Property(property="password", type="string", format="password"),
     *       @OA\Property(property="password_confirmation", type="string", format="password")
     *     )
     *   ),
     *
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, string $password): void {
                $user->password = $password;
                $user->setRememberToken(str()->random(60));
                $user->tokens()->delete();
                $user->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'message' => __($status),
            ], 200);
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }
}
