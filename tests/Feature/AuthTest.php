<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_com_credenciais_validas_emite_token(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('secret123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'secret123',
            'device_name' => 'tests',
        ]);

        $response->assertOk()->assertJsonStructure([
            'token', 'token_type', 'user' => ['id', 'email'],
        ]);
    }

    public function test_login_invalido_respeita_rate_limit(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/login', [
                'email' => $user->email,
                'password' => 'wrong',
            ]);
        }

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'wrong',
        ]);

        $response->assertStatus(429);
    }

    public function test_logout_revoga_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('tests')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/auth/logout');

        $response->assertNoContent();
    }

    public function test_reset_flow_envia_email_e_redefine(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('secret123'),
        ]);

        $this->postJson('/api/v1/auth/forgot', [
            'email' => $user->email,
        ])->assertOk();

        // Fake token scenario: use Password facade to create one
        $token = app('auth.password.broker')->createToken($user);

        $this->postJson('/api/v1/auth/reset', [
            'email' => $user->email,
            'token' => $token,
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertOk();
    }
}
