<?php

namespace Tests\Feature;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Jobs\TasksCsvExportJob;
use App\Models\Company;
use App\Models\Notification;
use App\Models\Phase;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TasksCsvExportJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Queue::fake();
    }

    public function test_job_gera_arquivo_csv_com_cabecalhos(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $project = Project::factory()->create(['company_id' => $company->id]);
        $phase = Phase::factory()->create(['project_id' => $project->id]);

        $task = Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'phase_id' => $phase->id,
            'title' => 'Tarefa Teste',
            'status' => TaskStatus::in_progress,
            'priority' => TaskPriority::high,
        ]);

        $job = new TasksCsvExportJob($user->id, $company->id, $project->id);
        $job->handle();

        // Verify file was created
        $files = Storage::disk('local')->files('exports');
        $this->assertCount(1, $files);

        $filePath = $files[0];
        $content = Storage::disk('local')->get($filePath);

        // Check UTF-8 BOM
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);

        // Check headers (skip BOM if present)
        $lines = explode("\n", trim($content));
        $firstLine = $lines[0];
        // Remove UTF-8 BOM if present
        if (str_starts_with($firstLine, "\xEF\xBB\xBF")) {
            $firstLine = substr($firstLine, 3);
        }
        $headers = str_getcsv($firstLine, ';');
        $expectedHeaders = [
            'ID',
            'Obra',
            'Fase',
            'Título',
            'Responsável',
            'Status',
            'Prioridade',
            'Data Início',
            'Data Fim',
            'Data Vencimento',
            'Atraso (dias)',
            'Iniciado em',
            'Concluído em',
            'Criado em',
            'Atualizado em',
        ];
        $this->assertEquals($expectedHeaders, $headers);
    }

    public function test_job_gera_csv_com_dados_das_tarefas(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $project = Project::factory()->create(['company_id' => $company->id, 'name' => 'Projeto Teste']);
        $phase = Phase::factory()->create(['project_id' => $project->id, 'name' => 'Fase Teste']);
        $assignee = User::factory()->create(['name' => 'João Silva']);

        $task = Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'phase_id' => $phase->id,
            'assignee_id' => $assignee->id,
            'title' => 'Tarefa Teste',
            'status' => TaskStatus::in_progress,
            'priority' => TaskPriority::high,
            'planned_start_at' => Carbon::parse('2024-01-01'),
            'planned_end_at' => Carbon::parse('2024-01-31'),
            'due_at' => Carbon::parse('2024-01-31'),
        ]);

        $job = new TasksCsvExportJob($user->id, $company->id, $project->id);
        $job->handle();

        $files = Storage::disk('local')->files('exports');
        $filePath = $files[0];
        $content = Storage::disk('local')->get($filePath);

        $lines = explode("\n", trim($content));
        $dataRow = str_getcsv($lines[1], ';');

        $this->assertEquals($task->id, (int) $dataRow[0]);
        $this->assertEquals('Projeto Teste', $dataRow[1]);
        $this->assertEquals('Fase Teste', $dataRow[2]);
        $this->assertEquals('Tarefa Teste', $dataRow[3]);
        $this->assertEquals('João Silva', $dataRow[4]);
        $this->assertEquals('Em Progresso', $dataRow[5]);
        $this->assertEquals('Alta', $dataRow[6]);
        $this->assertEquals('01/01/2024', $dataRow[7]);
        $this->assertEquals('31/01/2024', $dataRow[8]);
        $this->assertEquals('31/01/2024', $dataRow[9]);
    }

    public function test_job_aplica_filtros(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $project1 = Project::factory()->create(['company_id' => $company->id]);
        $project2 = Project::factory()->create(['company_id' => $company->id]);
        $phase = Phase::factory()->create(['project_id' => $project1->id]);

        Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project1->id,
            'phase_id' => $phase->id,
            'status' => TaskStatus::in_progress,
        ]);

        Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project2->id,
            'status' => TaskStatus::done,
        ]);

        $job = new TasksCsvExportJob(
            $user->id,
            $company->id,
            $project1->id,
            ['status' => 'in_progress']
        );
        $job->handle();

        $files = Storage::disk('local')->files('exports');
        $filePath = $files[0];
        $content = Storage::disk('local')->get($filePath);

        $lines = explode("\n", trim($content));
        // Header + 1 data row
        $this->assertCount(2, $lines);
    }

    public function test_job_cria_notificacao_para_usuario(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $project = Project::factory()->create(['company_id' => $company->id]);

        Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
        ]);

        $job = new TasksCsvExportJob($user->id, $company->id, $project->id);
        $job->handle();

        $notification = Notification::where('user_id', $user->id)
            ->where('type', 'export.completed')
            ->first();

        $this->assertNotNull($notification);
        $this->assertEquals('tasks', $notification->data['export_type']);
        $this->assertArrayHasKey('filename', $notification->data);
        $this->assertArrayHasKey('download_url', $notification->data);
        $this->assertArrayHasKey('row_count', $notification->data);
    }

    public function test_job_calcula_atraso_corretamente(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $project = Project::factory()->create(['company_id' => $company->id]);

        // Task overdue by 5 days
        $overdueTask = Task::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'status' => TaskStatus::in_progress,
            'due_at' => Carbon::now()->subDays(5),
        ]);

        $job = new TasksCsvExportJob($user->id, $company->id, $project->id);
        $job->handle();

        $files = Storage::disk('local')->files('exports');
        $filePath = $files[0];
        $content = Storage::disk('local')->get($filePath);

        $lines = explode("\n", trim($content));
        // Skip BOM if present in first line
        $dataLine = $lines[1];
        if (str_starts_with($dataLine, "\xEF\xBB\xBF")) {
            $dataLine = substr($dataLine, 3);
        }
        $dataRow = str_getcsv($dataLine, ';');

        // Atraso (dias) is at index 10
        // Note: delay is calculated as positive number of days overdue
        $this->assertEquals('5', $dataRow[10]);
    }
}

