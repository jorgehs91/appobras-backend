<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ExpoTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_expo_push_token(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $token = 'ExponentPushToken[xxxxxxxxxxxxxxxxxxxxxx]';

        $response = $this->postJson('/api/v1/user/expo-token', [
            'expo_push_token' => $token,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'expo_push_token',
                ],
            ])
            ->assertJson([
                'message' => 'Expo push token updated successfully',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'expo_push_token' => $token,
        ]);
    }

    public function test_user_can_update_expo_push_token(): void
    {
        $user = User::factory()->create([
            'expo_push_token' => 'ExponentPushToken[old-token]',
        ]);
        Sanctum::actingAs($user);

        $newToken = 'ExponentPushToken[new-token]';

        $response = $this->postJson('/api/v1/user/expo-token', [
            'expo_push_token' => $newToken,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'expo_push_token' => $newToken,
        ]);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
            'expo_push_token' => 'ExponentPushToken[old-token]',
        ]);
    }

    public function test_returns_422_for_invalid_token_format(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/user/expo-token', [
            'expo_push_token' => 'invalid-token-format',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['expo_push_token']);
    }

    public function test_returns_422_for_missing_token(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/user/expo-token', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['expo_push_token']);
    }

    public function test_returns_401_for_unauthenticated_user(): void
    {
        $response = $this->postJson('/api/v1/user/expo-token', [
            'expo_push_token' => 'ExponentPushToken[test]',
        ]);

        $response->assertStatus(401);
    }

    public function test_accepts_expo_push_token_format(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $token = 'ExpoPushToken[xxxxxxxxxxxxxxxxxxxxxx]';

        $response = $this->postJson('/api/v1/user/expo-token', [
            'expo_push_token' => $token,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'expo_push_token' => $token,
        ]);
    }

    public function test_token_can_be_nullable(): void
    {
        $user = User::factory()->create([
            'expo_push_token' => null,
        ]);
        Sanctum::actingAs($user);

        $token = 'ExponentPushToken[test]';

        $response = $this->postJson('/api/v1/user/expo-token', [
            'expo_push_token' => $token,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'expo_push_token' => $token,
        ]);
    }
}

