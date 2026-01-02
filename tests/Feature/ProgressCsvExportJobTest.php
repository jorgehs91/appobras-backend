<?php

namespace Tests\Feature;

use App\Jobs\ProgressCsvExportJob;
use App\Models\Company;
use App\Models\Notification;
use App\Models\Phase;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProgressCsvExportJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_job_gera_arquivo_csv_com_cabecalhos(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $project = Project::factory()->create(['company_id' => $company->id]);
        $phase = Phase::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'status' => 'active',
        ]);

        $job = new ProgressCsvExportJob($user->id, $company->id, $project->id);
        $job->handle();

        $files = Storage::disk('local')->files('exports');
        $this->assertCount(1, $files);

        $filePath = $files[0];
        $content = Storage::disk('local')->get($filePath);

        // Check UTF-8 BOM
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);

        // Check headers
        $lines = explode("\n", trim($content));
        $firstLine = $lines[0];
        if (str_starts_with($firstLine, "\xEF\xBB\xBF")) {
            $firstLine = substr($firstLine, 3);
        }
        $headers = str_getcsv($firstLine, ';');
        
        $this->assertContains('Obra', $headers);
        $this->assertContains('Fase', $headers);
        $this->assertContains('Progresso Fase (%)', $headers);
        $this->assertContains('Progresso Obra (%)', $headers);
    }

    public function test_job_gera_csv_com_dados_das_fases(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $project = Project::factory()->create(['company_id' => $company->id, 'name' => 'Projeto Teste']);
        $phase = Phase::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'name' => 'Fase Teste',
            'status' => 'active',
        ]);

        Task::factory()->count(3)->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'phase_id' => $phase->id,
        ]);

        $job = new ProgressCsvExportJob($user->id, $company->id, $project->id);
        $job->handle();

        $files = Storage::disk('local')->files('exports');
        $filePath = $files[0];
        $content = Storage::disk('local')->get($filePath);

        $lines = explode("\n", trim($content));
        $dataRow = str_getcsv($lines[1], ';');

        $this->assertEquals('Projeto Teste', $dataRow[0]);
        $this->assertEquals('Fase Teste', $dataRow[2]);
    }

    public function test_job_cria_notificacao_para_usuario(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $project = Project::factory()->create(['company_id' => $company->id]);
        $phase = Phase::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'status' => 'active',
        ]);

        $job = new ProgressCsvExportJob($user->id, $company->id, $project->id);
        $job->handle();

        $notification = Notification::where('user_id', $user->id)
            ->where('type', 'export.completed')
            ->first();

        $this->assertNotNull($notification);
        $this->assertEquals('progress', $notification->data['export_type']);
    }
}

