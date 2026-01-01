<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\File;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DocumentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_documents_for_project(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id, ['role' => 'Manager']);

        File::factory()->count(3)->document()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'fileable_id' => $project->id,
            'uploaded_by' => $user->id,
        ]);

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->getJson("/api/v1/projects/{$project->id}/documents");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_upload_document(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id, ['role' => 'Manager']);

        $file = UploadedFile::fake()->create('document.pdf', 1000, 'application/pdf');

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->postJson("/api/v1/projects/{$project->id}/documents", [
                'file' => $file,
                'name' => 'Test Document',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Test Document')
            ->assertJsonPath('data.mime_type', 'application/pdf');

        $this->assertDatabaseHas('files', [
            'name' => 'Test Document',
            'project_id' => $project->id,
            'company_id' => $company->id,
            'fileable_type' => Project::class,
            'fileable_id' => $project->id,
            'category' => 'document',
            'uploaded_by' => $user->id,
        ]);

        // Verify file was stored
        $document = File::query()->where('name', 'Test Document')->first();
        Storage::disk('local')->assertExists($document->path);
    }

    public function test_can_delete_document(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id, ['role' => 'Manager']);

        // Create a fake file
        $file = UploadedFile::fake()->create('document.pdf', 1000);
        $path = $file->store("documents/project-{$project->id}", 'local');

        $document = File::factory()->document()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'fileable_id' => $project->id,
            'uploaded_by' => $user->id,
            'path' => $path,
        ]);

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->deleteJson("/api/v1/documents/{$document->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('files', [
            'id' => $document->id,
            'deleted_at' => null,
        ]);
    }

    public function test_document_uploader_can_delete_own_document(): void
    {
        Storage::fake('local');

        $uploader = User::factory()->create();
        $company = Company::factory()->create();
        $uploader->companies()->attach($company->id);
        Sanctum::actingAs($uploader);

        $project = Project::factory()->create(['company_id' => $company->id]);
        $uploader->projects()->attach($project->id, ['role' => 'Manager']);

        $file = UploadedFile::fake()->create('document.pdf', 1000);
        $path = $file->store("documents/project-{$project->id}", 'local');

        $document = File::factory()->document()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'fileable_id' => $project->id,
            'uploaded_by' => $uploader->id,
            'path' => $path,
        ]);

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->deleteJson("/api/v1/documents/{$document->id}");

        $response->assertStatus(204);
    }

    public function test_cannot_upload_document_without_project_membership(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $project = Project::factory()->create(['company_id' => $company->id]);
        // User is NOT a member of this project

        $file = UploadedFile::fake()->create('document.pdf', 1000);

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->postJson("/api/v1/projects/{$project->id}/documents", [
                'file' => $file,
                'name' => 'Unauthorized Document',
            ]);

        $response->assertStatus(403);
    }

    public function test_cannot_delete_document_from_different_company(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();
        $user->companies()->attach($company1->id);
        Sanctum::actingAs($user);

        $project = Project::factory()->create(['company_id' => $company2->id]);

        $file = UploadedFile::fake()->create('document.pdf', 1000);
        $path = $file->store("documents/project-{$project->id}", 'local');

        $document = File::factory()->document()->create([
            'company_id' => $company2->id,
            'project_id' => $project->id,
            'fileable_id' => $project->id,
            'path' => $path,
        ]);

        $response = $this->withHeader('X-Company-Id', $company1->id)
            ->deleteJson("/api/v1/documents/{$document->id}");

        $response->assertStatus(403);
    }

    public function test_file_is_deleted_from_storage_on_destroy(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id, ['role' => 'Manager']);

        $file = UploadedFile::fake()->create('document.pdf', 1000);
        $path = $file->store("documents/project-{$project->id}", 'local');

        $document = File::factory()->document()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'fileable_id' => $project->id,
            'uploaded_by' => $user->id,
            'path' => $path,
        ]);

        // Verify file exists before deletion
        Storage::disk('local')->assertExists($path);

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->deleteJson("/api/v1/documents/{$document->id}");

        $response->assertStatus(204);

        // Verify file was deleted from storage
        Storage::disk('local')->assertMissing($path);
    }
}
