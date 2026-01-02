<?php

namespace Tests\Feature;

use App\Jobs\PvxrCsvExportJob;
use App\Models\Budget;
use App\Models\Company;
use App\Models\CostItem;
use App\Models\Notification;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PvxrCsvExportJobTest extends TestCase
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
        $budget = Budget::factory()->create(['project_id' => $project->id]);

        $job = new PvxrCsvExportJob($user->id, $company->id, $project->id);
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
        $this->assertContains('Cost Item', $headers);
        $this->assertContains('Previsto', $headers);
        $this->assertContains('Realizado', $headers);
        $this->assertContains('Variação', $headers);
    }

    public function test_job_cria_notificacao_para_usuario(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $project = Project::factory()->create(['company_id' => $company->id]);
        $budget = Budget::factory()->create(['project_id' => $project->id]);

        $job = new PvxrCsvExportJob($user->id, $company->id, $project->id);
        $job->handle();

        $notification = Notification::where('user_id', $user->id)
            ->where('type', 'export.completed')
            ->first();

        $this->assertNotNull($notification);
        $this->assertEquals('pvxrv', $notification->data['export_type']);
    }
}

