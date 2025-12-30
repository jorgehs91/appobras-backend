<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Notification;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_listar_notifications_requer_autenticacao(): void
    {
        $this->getJson('/api/v1/notifications')
            ->assertStatus(401);
    }

    public function test_listar_notifications_do_usuario(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        $project = Project::factory()->create(['company_id' => $company->id]);

        // Criar notificações para o usuário
        $notification1 = Notification::factory()->create([
            'user_id' => $user->id,
            'type' => 'task.overdue',
            'read_at' => null,
        ]);

        $notification2 = Notification::factory()->create([
            'user_id' => $user->id,
            'type' => 'task.near_due',
            'read_at' => now(),
        ]);

        // Criar notificação para outro usuário (não deve aparecer)
        $otherUser = User::factory()->create();
        Notification::factory()->create([
            'user_id' => $otherUser->id,
            'type' => 'task.overdue',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/notifications')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'type', 'data', 'is_read', 'read_at', 'created_at'],
                ],
                'meta' => ['unread_count', 'current_page', 'per_page', 'total', 'last_page'],
            ])
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.unread_count', 1)
            ->assertJsonPath('meta.total', 2);

        $ids = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertContains($notification1->id, $ids);
        $this->assertContains($notification2->id, $ids);
    }

    public function test_listar_notifications_com_paginacao(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);

        // Criar 25 notificações
        Notification::factory()->count(25)->create([
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/notifications?per_page=10&page=1')
            ->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.total', 25)
            ->assertJsonPath('meta.last_page', 3);
    }

    public function test_filtrar_notifications_por_read(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);

        // Criar notificações lidas e não lidas
        $unread1 = Notification::factory()->create([
            'user_id' => $user->id,
            'read_at' => null,
        ]);

        $unread2 = Notification::factory()->create([
            'user_id' => $user->id,
            'read_at' => null,
        ]);

        $read1 = Notification::factory()->create([
            'user_id' => $user->id,
            'read_at' => now(),
        ]);

        Sanctum::actingAs($user);

        // Filtrar apenas não lidas
        $response = $this->getJson('/api/v1/notifications?read=false')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.unread_count', 2);

        $ids = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertContains($unread1->id, $ids);
        $this->assertContains($unread2->id, $ids);
        $this->assertNotContains($read1->id, $ids);

        // Filtrar apenas lidas
        $response = $this->getJson('/api/v1/notifications?read=true')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $read1->id);
    }

    public function test_filtrar_notifications_por_tipo(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);

        $overdueNotification = Notification::factory()->create([
            'user_id' => $user->id,
            'type' => 'task.overdue',
        ]);

        $nearDueNotification = Notification::factory()->create([
            'user_id' => $user->id,
            'type' => 'task.near_due',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/notifications?type=task.overdue')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $overdueNotification->id)
            ->assertJsonPath('data.0.type', 'task.overdue');
    }

    public function test_contador_unread_count_esta_correto(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);

        // Criar 5 não lidas e 3 lidas
        Notification::factory()->count(5)->create([
            'user_id' => $user->id,
            'read_at' => null,
        ]);

        Notification::factory()->count(3)->create([
            'user_id' => $user->id,
            'read_at' => now(),
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/notifications')
            ->assertOk()
            ->assertJsonPath('meta.unread_count', 5);
    }

    public function test_marcar_notification_como_lida(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);

        $notification = Notification::factory()->create([
            'user_id' => $user->id,
            'read_at' => null,
        ]);

        Sanctum::actingAs($user);

        $this->patchJson("/api/v1/notifications/{$notification->id}/read")
            ->assertStatus(204);

        $notification->refresh();
        $this->assertNotNull($notification->read_at);
        $this->assertTrue($notification->isRead());
    }

    public function test_marcar_notification_como_lida_retorna_404_se_nao_existe(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->patchJson('/api/v1/notifications/99999/read')
            ->assertStatus(404);
    }

    public function test_marcar_notification_como_lida_retorna_404_se_pertence_outro_usuario(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        $otherUser->companies()->attach($company->id);

        $notification = Notification::factory()->create([
            'user_id' => $otherUser->id,
            'read_at' => null,
        ]);

        Sanctum::actingAs($user);

        $this->patchJson("/api/v1/notifications/{$notification->id}/read")
            ->assertStatus(404);
    }

    public function test_marcar_notification_ja_lida_nao_gera_erro(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);

        $notification = Notification::factory()->create([
            'user_id' => $user->id,
            'read_at' => now()->subDay(),
        ]);

        Sanctum::actingAs($user);

        $this->patchJson("/api/v1/notifications/{$notification->id}/read")
            ->assertStatus(204);

        // Verificar que read_at não mudou
        $originalReadAt = $notification->read_at;
        $notification->refresh();
        $this->assertEquals($originalReadAt->format('Y-m-d H:i:s'), $notification->read_at->format('Y-m-d H:i:s'));
    }

    public function test_per_page_maximo_e_100(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);

        Notification::factory()->count(150)->create([
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/notifications?per_page=200')
            ->assertOk()
            ->assertJsonPath('meta.per_page', 100); // Deve limitar a 100
    }
}

