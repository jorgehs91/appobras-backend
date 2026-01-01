<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\File;
use App\Models\Phase;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AttachmentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_attachments_for_task(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id, ['role' => 'Manager']);

        $phase = Phase::factory()->create(['project_id' => $project->id]);
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'phase_id' => $phase->id,
            'company_id' => $company->id,
        ]);

        File::factory()->count(3)->create([
            'fileable_type' => Task::class,
            'fileable_id' => $task->id,
            'company_id' => $company->id,
            'project_id' => $project->id,
            'category' => 'attachment',
            'uploaded_by' => $user->id,
        ]);

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->getJson("/api/v1/tasks/{$task->id}/attachments");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_upload_attachment(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id, ['role' => 'Manager']);

        $phase = Phase::factory()->create(['project_id' => $project->id]);
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'phase_id' => $phase->id,
            'company_id' => $company->id,
        ]);

        $file = UploadedFile::fake()->create('attachment.pdf', 1000, 'application/pdf');

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->postJson("/api/v1/tasks/{$task->id}/attachments", [
                'file' => $file,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.filename', 'attachment.pdf')
            ->assertJsonPath('data.mime_type', 'application/pdf');

        $this->assertDatabaseHas('files', [
            'name' => 'attachment.pdf',
            'fileable_type' => Task::class,
            'fileable_id' => $task->id,
            'category' => 'attachment',
            'uploaded_by' => $user->id,
        ]);

        // Verify file was stored
        $attachment = File::query()->where('name', 'attachment.pdf')->first();
        Storage::disk('local')->assertExists($attachment->path);
    }

    public function test_can_show_attachment(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id, ['role' => 'Manager']);

        $phase = Phase::factory()->create(['project_id' => $project->id]);
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'phase_id' => $phase->id,
            'company_id' => $company->id,
        ]);

        $attachment = File::factory()->create([
            'fileable_type' => Task::class,
            'fileable_id' => $task->id,
            'company_id' => $company->id,
            'project_id' => $project->id,
            'category' => 'attachment',
            'uploaded_by' => $user->id,
        ]);

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->getJson("/api/v1/attachments/{$attachment->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $attachment->id)
            ->assertJsonPath('data.task_id', $task->id);
    }

    public function test_can_download_attachment(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id, ['role' => 'Manager']);

        $phase = Phase::factory()->create(['project_id' => $project->id]);
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'phase_id' => $phase->id,
            'company_id' => $company->id,
        ]);

        $file = UploadedFile::fake()->create('attachment.pdf', 1000);
        $path = $file->store("attachments/task-{$task->id}", 'local');

        $attachment = File::factory()->create([
            'fileable_type' => Task::class,
            'fileable_id' => $task->id,
            'company_id' => $company->id,
            'project_id' => $project->id,
            'category' => 'attachment',
            'uploaded_by' => $user->id,
            'path' => $path,
            'name' => 'attachment.pdf',
        ]);

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->getJson("/api/v1/attachments/{$attachment->id}/download");

        $response->assertStatus(200)
            ->assertHeader('Content-Type', $attachment->mime_type);
    }

    public function test_can_delete_attachment(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id, ['role' => 'Manager']);

        $phase = Phase::factory()->create(['project_id' => $project->id]);
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'phase_id' => $phase->id,
            'company_id' => $company->id,
        ]);

        // Create a fake file
        $file = UploadedFile::fake()->create('attachment.pdf', 1000);
        $path = $file->store("attachments/task-{$task->id}", 'local');

        $attachment = File::factory()->create([
            'fileable_type' => Task::class,
            'fileable_id' => $task->id,
            'company_id' => $company->id,
            'project_id' => $project->id,
            'category' => 'attachment',
            'uploaded_by' => $user->id,
            'path' => $path,
        ]);

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->deleteJson("/api/v1/attachments/{$attachment->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('files', [
            'id' => $attachment->id,
            'deleted_at' => null,
        ]);
    }

    public function test_attachment_uploader_can_delete_own_attachment(): void
    {
        Storage::fake('local');

        $uploader = User::factory()->create();
        $company = Company::factory()->create();
        $uploader->companies()->attach($company->id);
        Sanctum::actingAs($uploader);

        $project = Project::factory()->create(['company_id' => $company->id]);
        $uploader->projects()->attach($project->id, ['role' => 'Manager']);

        $phase = Phase::factory()->create(['project_id' => $project->id]);
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'phase_id' => $phase->id,
            'company_id' => $company->id,
        ]);

        $file = UploadedFile::fake()->create('attachment.pdf', 1000);
        $path = $file->store("attachments/task-{$task->id}", 'local');

        $attachment = File::factory()->create([
            'fileable_type' => Task::class,
            'fileable_id' => $task->id,
            'company_id' => $company->id,
            'project_id' => $project->id,
            'category' => 'attachment',
            'uploaded_by' => $uploader->id,
            'path' => $path,
        ]);

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->deleteJson("/api/v1/attachments/{$attachment->id}");

        $response->assertStatus(204);
    }

    public function test_cannot_upload_attachment_without_project_membership(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $project = Project::factory()->create(['company_id' => $company->id]);
        // User is NOT a member of this project

        $phase = Phase::factory()->create(['project_id' => $project->id]);
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'phase_id' => $phase->id,
            'company_id' => $company->id,
        ]);

        $file = UploadedFile::fake()->create('attachment.pdf', 1000);

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->postJson("/api/v1/tasks/{$task->id}/attachments", [
                'file' => $file,
            ]);

        $response->assertStatus(403);
    }

    public function test_cannot_delete_attachment_from_different_company(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();
        $user->companies()->attach($company1->id);
        Sanctum::actingAs($user);

        $project = Project::factory()->create(['company_id' => $company2->id]);

        $phase = Phase::factory()->create(['project_id' => $project->id]);
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'phase_id' => $phase->id,
            'company_id' => $company2->id,
        ]);

        $file = UploadedFile::fake()->create('attachment.pdf', 1000);
        $path = $file->store("attachments/task-{$task->id}", 'local');

        $attachment = File::factory()->create([
            'fileable_type' => Task::class,
            'fileable_id' => $task->id,
            'company_id' => $company2->id,
            'project_id' => $project->id,
            'category' => 'attachment',
            'path' => $path,
        ]);

        $response = $this->withHeader('X-Company-Id', $company1->id)
            ->deleteJson("/api/v1/attachments/{$attachment->id}");

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

        $phase = Phase::factory()->create(['project_id' => $project->id]);
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'phase_id' => $phase->id,
            'company_id' => $company->id,
        ]);

        $file = UploadedFile::fake()->create('attachment.pdf', 1000);
        $path = $file->store("attachments/task-{$task->id}", 'local');

        $attachment = File::factory()->create([
            'fileable_type' => Task::class,
            'fileable_id' => $task->id,
            'company_id' => $company->id,
            'project_id' => $project->id,
            'category' => 'attachment',
            'uploaded_by' => $user->id,
            'path' => $path,
        ]);

        // Verify file exists before deletion
        Storage::disk('local')->assertExists($path);

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->deleteJson("/api/v1/attachments/{$attachment->id}");

        $response->assertStatus(204);

        // Verify file was deleted from storage
        Storage::disk('local')->assertMissing($path);
    }

    public function test_validates_file_upload_requirements(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id, ['role' => 'Manager']);

        $phase = Phase::factory()->create(['project_id' => $project->id]);
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'phase_id' => $phase->id,
            'company_id' => $company->id,
        ]);

        // Try to upload without file
        $response = $this->withHeader('X-Company-Id', $company->id)
            ->postJson("/api/v1/tasks/{$task->id}/attachments", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    public function test_validates_file_size_limit(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id, ['role' => 'Manager']);

        $phase = Phase::factory()->create(['project_id' => $project->id]);
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'phase_id' => $phase->id,
            'company_id' => $company->id,
        ]);

        // Try to upload file larger than 10MB
        $file = UploadedFile::fake()->create('attachment.pdf', 11000); // 11MB

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->postJson("/api/v1/tasks/{$task->id}/attachments", [
                'file' => $file,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }
}
