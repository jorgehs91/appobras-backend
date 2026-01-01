<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Phase;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskCommentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_comments_for_task(): void
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

        TaskComment::factory()->count(3)->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
        ]);

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->getJson("/api/v1/tasks/{$task->id}/comments");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_list_comments_ordered_by_created_at_asc(): void
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

        $comment1 = TaskComment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'created_at' => now()->subDays(2),
        ]);

        $comment2 = TaskComment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'created_at' => now()->subDays(1),
        ]);

        $comment3 = TaskComment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'created_at' => now(),
        ]);

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->getJson("/api/v1/tasks/{$task->id}/comments?order_by=asc");

        $response->assertStatus(200)
            ->assertJsonPath('data.0.id', $comment1->id)
            ->assertJsonPath('data.1.id', $comment2->id)
            ->assertJsonPath('data.2.id', $comment3->id);
    }

    public function test_can_list_comments_ordered_by_created_at_desc(): void
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

        $comment1 = TaskComment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'created_at' => now()->subDays(2),
        ]);

        $comment2 = TaskComment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'created_at' => now()->subDays(1),
        ]);

        $comment3 = TaskComment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'created_at' => now(),
        ]);

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->getJson("/api/v1/tasks/{$task->id}/comments?order_by=desc");

        $response->assertStatus(200)
            ->assertJsonPath('data.0.id', $comment3->id)
            ->assertJsonPath('data.1.id', $comment2->id)
            ->assertJsonPath('data.2.id', $comment1->id);
    }

    public function test_can_create_comment(): void
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

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->postJson("/api/v1/tasks/{$task->id}/comments", [
                'body' => 'Este é um comentário de teste',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.body', 'Este é um comentário de teste')
            ->assertJsonPath('data.user_id', $user->id)
            ->assertJsonPath('data.task_id', $task->id);

        $this->assertDatabaseHas('task_comments', [
            'task_id' => $task->id,
            'user_id' => $user->id,
            'body' => 'Este é um comentário de teste',
        ]);
    }

    public function test_can_show_comment(): void
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

        $comment = TaskComment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'body' => 'Comentário de teste',
        ]);

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->getJson("/api/v1/tasks/{$task->id}/comments/{$comment->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $comment->id)
            ->assertJsonPath('data.body', 'Comentário de teste')
            ->assertJsonPath('data.task_id', $task->id);
    }

    public function test_can_update_own_comment(): void
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

        $comment = TaskComment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'body' => 'Comentário original',
        ]);

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->putJson("/api/v1/tasks/{$task->id}/comments/{$comment->id}", [
                'body' => 'Comentário atualizado',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.body', 'Comentário atualizado');

        $this->assertDatabaseHas('task_comments', [
            'id' => $comment->id,
            'body' => 'Comentário atualizado',
        ]);
    }

    public function test_can_update_comment_with_reactions(): void
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

        $comment = TaskComment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
        ]);

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->putJson("/api/v1/tasks/{$task->id}/comments/{$comment->id}", [
                'reactions' => ['like' => 5, 'love' => 2],
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.reactions.like', 5)
            ->assertJsonPath('data.reactions.love', 2);

        $this->assertDatabaseHas('task_comments', [
            'id' => $comment->id,
            'reactions' => json_encode(['like' => 5, 'love' => 2]),
        ]);
    }

    public function test_cannot_update_other_user_comment(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        $otherUser->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id, ['role' => 'Manager']);
        $otherUser->projects()->attach($project->id, ['role' => 'Manager']);

        $phase = Phase::factory()->create(['project_id' => $project->id]);
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'phase_id' => $phase->id,
            'company_id' => $company->id,
        ]);

        $comment = TaskComment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $otherUser->id, // Comment belongs to other user
            'body' => 'Comentário original',
        ]);

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->putJson("/api/v1/tasks/{$task->id}/comments/{$comment->id}", [
                'body' => 'Tentativa de atualizar',
            ]);

        $response->assertStatus(403);

        $this->assertDatabaseHas('task_comments', [
            'id' => $comment->id,
            'body' => 'Comentário original', // Should remain unchanged
        ]);
    }

    public function test_can_delete_own_comment(): void
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

        $comment = TaskComment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
        ]);

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->deleteJson("/api/v1/tasks/{$task->id}/comments/{$comment->id}");

        $response->assertStatus(204);

        $this->assertSoftDeleted('task_comments', [
            'id' => $comment->id,
        ]);
    }

    public function test_cannot_delete_other_user_comment(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        $otherUser->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id, ['role' => 'Manager']);
        $otherUser->projects()->attach($project->id, ['role' => 'Manager']);

        $phase = Phase::factory()->create(['project_id' => $project->id]);
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'phase_id' => $phase->id,
            'company_id' => $company->id,
        ]);

        $comment = TaskComment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $otherUser->id, // Comment belongs to other user
        ]);

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->deleteJson("/api/v1/tasks/{$task->id}/comments/{$comment->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('task_comments', [
            'id' => $comment->id,
            'deleted_at' => null, // Should not be deleted
        ]);
    }

    public function test_cannot_create_comment_without_project_membership(): void
    {
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

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->postJson("/api/v1/tasks/{$task->id}/comments", [
                'body' => 'Tentativa de comentar',
            ]);

        $response->assertStatus(403);
    }

    public function test_cannot_access_comment_from_different_company(): void
    {
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

        $comment = TaskComment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
        ]);

        $response = $this->withHeader('X-Company-Id', $company1->id)
            ->getJson("/api/v1/tasks/{$task->id}/comments/{$comment->id}");

        $response->assertStatus(403);
    }

    public function test_validates_comment_body_required(): void
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

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->postJson("/api/v1/tasks/{$task->id}/comments", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['body']);
    }

    public function test_validates_comment_body_max_length(): void
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

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->postJson("/api/v1/tasks/{$task->id}/comments", [
                'body' => str_repeat('a', 10001), // Exceeds max length
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['body']);
    }

    public function test_returns_404_when_comment_does_not_belong_to_task(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id);
        Sanctum::actingAs($user);

        $project = Project::factory()->create(['company_id' => $company->id]);
        $user->projects()->attach($project->id, ['role' => 'Manager']);

        $phase = Phase::factory()->create(['project_id' => $project->id]);
        $task1 = Task::factory()->create([
            'project_id' => $project->id,
            'phase_id' => $phase->id,
            'company_id' => $company->id,
        ]);

        $task2 = Task::factory()->create([
            'project_id' => $project->id,
            'phase_id' => $phase->id,
            'company_id' => $company->id,
        ]);

        $comment = TaskComment::factory()->create([
            'task_id' => $task2->id, // Comment belongs to task2
            'user_id' => $user->id,
        ]);

        $response = $this->withHeader('X-Company-Id', $company->id)
            ->getJson("/api/v1/tasks/{$task1->id}/comments/{$comment->id}"); // Trying to access via task1

        $response->assertStatus(404);
    }
}

