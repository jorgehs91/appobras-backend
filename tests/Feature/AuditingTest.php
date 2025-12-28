<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Contractor;
use App\Models\Document;
use App\Models\Phase;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuditingTest extends TestCase
{
    use RefreshDatabase;

    public function test_project_auditing_fields_are_populated_on_create(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/projects', [
            'name' => 'Test Project',
            'description' => 'Test Description',
        ], ['X-Company-Id' => $company->id])
            ->assertCreated();

        $projectId = $response->json('data.id');
        $project = Project::query()->find($projectId);

        $this->assertNotNull($project->created_by);
        $this->assertEquals($user->id, $project->created_by);
        $this->assertNull($project->updated_by); // Não deve ter updated_by em criação
    }

    public function test_project_auditing_fields_are_populated_on_update(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        // Criar projeto
        $response = $this->postJson('/api/v1/projects', [
            'name' => 'Test Project',
        ], ['X-Company-Id' => $company->id])
            ->assertCreated();

        $projectId = $response->json('data.id');
        $project = Project::query()->find($projectId);
        $originalCreatedBy = $project->created_by;

        // Atualizar projeto
        $this->patchJson("/api/v1/projects/{$projectId}", [
            'name' => 'Updated Project Name',
        ], ['X-Company-Id' => $company->id])
            ->assertOk();

        $project->refresh();

        $this->assertEquals($originalCreatedBy, $project->created_by); // created_by não muda
        $this->assertNotNull($project->updated_by);
        $this->assertEquals($user->id, $project->updated_by);
    }

    public function test_phase_auditing_fields_are_populated(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id, ['role' => 'Manager']);
        Sanctum::actingAs($user);

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->postJson("/api/v1/projects/{$project->id}/phases", [
                'name' => 'Test Phase',
            ])
            ->assertCreated();

        $phaseId = $response->json('data.id');
        $phase = Phase::query()->find($phaseId);

        $this->assertNotNull($phase->created_by);
        $this->assertEquals($user->id, $phase->created_by);

        // Atualizar fase
        $this->withHeader('X-Company-Id', $company->id)
            ->putJson("/api/v1/phases/{$phaseId}", [
                'name' => 'Updated Phase',
            ])
            ->assertOk();

        $phase->refresh();
        $this->assertNotNull($phase->updated_by);
        $this->assertEquals($user->id, $phase->updated_by);
    }

    public function test_task_auditing_fields_are_populated(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id, ['role' => 'Manager']);
        $phase = Phase::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
        ]);
        Sanctum::actingAs($user);

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->postJson("/api/v1/projects/{$project->id}/tasks", [
                'title' => 'Test Task',
                'phase_id' => $phase->id,
            ])
            ->assertCreated();

        $taskId = $response->json('data.id');
        $task = Task::query()->find($taskId);

        $this->assertNotNull($task->created_by);
        $this->assertEquals($user->id, $task->created_by);

        // Atualizar task
        $this->withHeader('X-Company-Id', $company->id)
            ->putJson("/api/v1/tasks/{$taskId}", [
                'title' => 'Updated Task',
            ])
            ->assertOk();

        $task->refresh();
        $this->assertNotNull($task->updated_by);
        $this->assertEquals($user->id, $task->updated_by);
    }

    public function test_document_auditing_fields_are_populated(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id, ['role' => 'Manager']);
        Sanctum::actingAs($user);

        $file = UploadedFile::fake()->create('document.pdf', 1000, 'application/pdf');

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->postJson("/api/v1/projects/{$project->id}/documents", [
                'file' => $file,
                'name' => 'Test Document',
            ])
            ->assertCreated();

        $documentId = $response->json('data.id');
        $document = Document::query()->find($documentId);

        $this->assertNotNull($document->created_by);
        $this->assertEquals($user->id, $document->created_by);
    }

    public function test_contractor_auditing_fields_are_populated(): void
    {
        $user = User::factory()->create();
        $company = Company::query()->create(['name' => 'Test Company']);
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/contractors', [
            'name' => 'Test Contractor',
            'contact' => 'test@example.com',
        ], ['X-Company-Id' => $company->id])
            ->assertCreated();

        $contractorId = $response->json('data.id');
        $contractor = Contractor::query()->find($contractorId);

        $this->assertNotNull($contractor->created_by);
        $this->assertEquals($user->id, $contractor->created_by);

        // Atualizar contractor
        $this->putJson("/api/v1/contractors/{$contractorId}", [
            'name' => 'Updated Contractor',
        ], ['X-Company-Id' => $company->id])
            ->assertOk();

        $contractor->refresh();
        $this->assertNotNull($contractor->updated_by);
        $this->assertEquals($user->id, $contractor->updated_by);
    }

    public function test_auditing_fields_are_null_when_created_without_auth(): void
    {
        // Criar model sem contexto de autenticação (como em seeders)
        $company = Company::factory()->create();
        $project = Project::factory()->create([
            'company_id' => $company->id,
        ]);

        $this->assertNull($project->created_by);
        $this->assertNull($project->updated_by);
    }

    public function test_auditing_fields_different_users_for_create_and_update(): void
    {
        $creator = User::factory()->create();
        $updater = User::factory()->create();
        $company = Company::factory()->create();
        $creator->companies()->attach($company->id);
        $updater->companies()->attach($company->id);

        // Criar como creator
        Sanctum::actingAs($creator);
        $response = $this->withHeader('X-Company-Id', $company->id)
            ->postJson('/api/v1/projects', [
                'name' => 'Test Project',
            ])
            ->assertCreated();

        $projectId = $response->json('data.id');
        $project = Project::query()->find($projectId);
        $this->assertEquals($creator->id, $project->created_by);

        // Atualizar como updater (precisa ser membro do projeto)
        $updater->projects()->attach($projectId, ['role' => 'Manager']);
        Sanctum::actingAs($updater);
        $this->withHeader('X-Company-Id', $company->id)
            ->patchJson("/api/v1/projects/{$projectId}", [
                'name' => 'Updated Project',
            ])
            ->assertOk();

        $project->refresh();
        $this->assertEquals($creator->id, $project->created_by);
        $this->assertEquals($updater->id, $project->updated_by);
    }
}
