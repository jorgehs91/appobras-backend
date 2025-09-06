<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_registro_cria_conta_e_emite_token(): void
    {
        $payload = [
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'password-123',
            'password_confirmation' => 'password-123',
            'device_name' => 'tests',
        ];

        $response = $this->postJson('/api/v1/auth/register', $payload);

        $response->assertCreated()->assertJsonStructure([
            'token', 'token_type', 'user' => ['id', 'email'],
        ]);

        $this->assertDatabaseHas('users', ['email' => 'new@example.com']);
    }

    public function test_registro_respeita_rate_limit(): void
    {
        // disparar 5x e a 6a deve ser 429
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/register', [
                'name' => 'User '.$i,
                'email' => 'u'.$i.'@example.com',
                'password' => 'password-123',
                'password_confirmation' => 'password-123',
            ]);
        }

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'User 6',
            'email' => 'u6@example.com',
            'password' => 'password-123',
            'password_confirmation' => 'password-123',
        ]);

        $response->assertStatus(429);
    }
}
