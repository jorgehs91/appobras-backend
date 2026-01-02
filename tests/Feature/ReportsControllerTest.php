<?php

namespace Tests\Feature;

use App\Enums\SystemRole;
use App\Enums\TaskStatus;
use App\Models\Company;
use App\Models\Notification;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReportsControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Queue::fake();

        // Create roles
        Role::create(['name' => SystemRole::Financeiro->value, 'guard_name' => 'sanctum']);
        Role::create(['name' => SystemRole::AdminObra->value, 'guard_name' => 'sanctum']);
        Role::create(['name' => SystemRole::Engenheiro->value, 'guard_name' => 'sanctum']);
    }

    public function test_export_tasks_requer_autenticacao(): void
    {
        $this->postJson('/api/v1/reports/tasks/export')
            ->assertUnauthorized();
    }

    public function test_export_tasks_requer_company_id(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/reports/tasks/export')
            ->assertStatus(403);
    }

    public function test_export_tasks_inicia_job(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/reports/tasks/export', [
            'project_id' => 1,
        ], [
            'X-Company-Id' => $company->id,
        ])
            ->assertStatus(202)
            ->assertJson([
                'message' => 'Exportação iniciada. Você receberá uma notificação quando o arquivo estiver pronto.',
                'report_type' => 'tasks',
            ]);

        Queue::assertPushed(\App\Jobs\TasksCsvExportJob::class, function ($job) use ($user, $company) {
            return $job->userId === $user->id
                && $job->companyId === $company->id
                && $job->projectId === 1;
        });
    }

    public function test_export_tasks_com_filtros(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/reports/tasks/export', [
            'project_id' => 1,
            'phase_id' => 2,
            'status' => 'in_progress',
            'assignee_id' => 3,
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
            'overdue' => true,
        ], [
            'X-Company-Id' => $company->id,
        ])
            ->assertStatus(202);

        Queue::assertPushed(\App\Jobs\TasksCsvExportJob::class, function ($job) {
            return isset($job->filters['phase_id'])
                && isset($job->filters['status'])
                && isset($job->filters['assignee_id'])
                && isset($job->filters['start_date'])
                && isset($job->filters['end_date'])
                && isset($job->filters['overdue']);
        });
    }

    public function test_export_tipo_invalido_retorna_erro(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/reports/invalid-type/export', [], [
            'X-Company-Id' => $company->id,
        ])
            ->assertStatus(400)
            ->assertJson([
                'message' => 'Tipo de relatório inválido',
            ]);
    }

    public function test_download_arquivo_requer_autenticacao(): void
    {
        $this->getJson('/api/v1/reports/download/test.csv')
            ->assertUnauthorized();
    }

    public function test_download_arquivo_inexistente_retorna_404(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);

        Sanctum::actingAs($user);

        Storage::disk('local')->put('exports/test.csv', 'test content');

        $this->getJson('/api/v1/reports/download/nonexistent.csv', [
            'X-Company-Id' => $company->id,
        ])
            ->assertStatus(404);
    }

    public function test_download_arquivo_sem_notificacao_retorna_403(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);

        Sanctum::actingAs($user);

        Storage::disk('local')->put('exports/test.csv', 'test content');

        $this->getJson('/api/v1/reports/download/test.csv', [
            'X-Company-Id' => $company->id,
        ])
            ->assertStatus(403)
            ->assertJson([
                'message' => 'Você não tem permissão para baixar este arquivo',
            ]);
    }

    public function test_download_arquivo_com_notificacao_retorna_arquivo(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);

        Sanctum::actingAs($user);

        $filename = 'tasks_2024-01-01_12345678_abcdefgh.csv';
        $content = "ID,Obra,Fase,Título\n1,Projeto 1,Fase 1,Tarefa 1";
        Storage::disk('local')->put("exports/{$filename}", $content);

        Notification::create([
            'user_id' => $user->id,
            'notifiable_id' => $user->id,
            'notifiable_type' => \App\Models\User::class,
            'type' => 'export.completed',
            'data' => [
                'export_type' => 'tasks',
                'filename' => $filename,
                'file_path' => "exports/{$filename}",
            ],
            'channels' => ['database'],
        ]);

        $response = $this->get("/api/v1/reports/download/{$filename}", [
            'X-Company-Id' => $company->id,
        ])
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $responseContent = $response->streamedContent();
        $this->assertEquals($content, $responseContent);
        $this->assertStringContainsString("attachment; filename=\"{$filename}\"", $response->headers->get('Content-Disposition'));
    }

    public function test_download_arquivo_de_outro_usuario_retorna_403(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $company = Company::factory()->create();
        $user1->companies()->attach($company->id);
        $user2->companies()->attach($company->id);

        Sanctum::actingAs($user2);

        $filename = 'tasks_2024-01-01_12345678_abcdefgh.csv';
        Storage::disk('local')->put("exports/{$filename}", 'test content');

        // Notification belongs to user1, but user2 is trying to download
        Notification::create([
            'user_id' => $user1->id,
            'notifiable_id' => $user1->id,
            'notifiable_type' => \App\Models\User::class,
            'type' => 'export.completed',
            'data' => [
                'export_type' => 'tasks',
                'filename' => $filename,
                'file_path' => "exports/{$filename}",
            ],
            'channels' => ['database'],
        ]);

        $this->getJson("/api/v1/reports/download/{$filename}", [
            'X-Company-Id' => $company->id,
        ])
            ->assertStatus(403);
    }
}

