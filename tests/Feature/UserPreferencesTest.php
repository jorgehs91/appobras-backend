<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPreferencesTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_email_notifications_preference(): void
    {
        $user = User::factory()->create(['email_notifications_enabled' => true]);

        $response = $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/user/preferences', [
                'email_notifications_enabled' => false,
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'email',
                'email_notifications_enabled',
            ],
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email_notifications_enabled' => false,
        ]);

        $this->assertFalse($response->json('data.email_notifications_enabled'));
    }

    public function test_user_can_enable_email_notifications(): void
    {
        $user = User::factory()->create(['email_notifications_enabled' => false]);

        $response = $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/user/preferences', [
                'email_notifications_enabled' => true,
            ]);

        $response->assertStatus(200);
        $this->assertTrue($response->json('data.email_notifications_enabled'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email_notifications_enabled' => true,
        ]);
    }

    public function test_endpoint_requires_authentication(): void
    {
        $response = $this->putJson('/api/v1/user/preferences', [
            'email_notifications_enabled' => false,
        ]);

        $response->assertStatus(401);
    }

    public function test_endpoint_validates_boolean_value(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/user/preferences', [
                'email_notifications_enabled' => 'not-a-boolean',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email_notifications_enabled']);
    }
}

