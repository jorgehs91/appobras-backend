<?php

namespace Tests\Unit;

use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskCommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_comment_can_be_created_with_valid_data(): void
    {
        $task = Task::factory()->create();
        $user = User::factory()->create();

        $comment = TaskComment::factory()->create([
            'task_id' => $task->id,
            'user_id' => $user->id,
            'body' => 'Este é um comentário de teste',
        ]);

        $this->assertDatabaseHas('task_comments', [
            'id' => $comment->id,
            'task_id' => $task->id,
            'user_id' => $user->id,
            'body' => 'Este é um comentário de teste',
        ]);
    }

    public function test_task_comment_has_task_relationship(): void
    {
        $task = Task::factory()->create();
        $comment = TaskComment::factory()->create([
            'task_id' => $task->id,
        ]);

        $this->assertEquals($task->id, $comment->task->id);
        $this->assertInstanceOf(Task::class, $comment->task);
    }

    public function test_task_comment_has_user_relationship(): void
    {
        $user = User::factory()->create();
        $comment = TaskComment::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertEquals($user->id, $comment->user->id);
        $this->assertInstanceOf(User::class, $comment->user);
    }

    public function test_task_comment_reactions_are_cast_to_array(): void
    {
        $reactions = ['like' => 5, 'love' => 2];
        $comment = TaskComment::factory()->withReactions($reactions)->create();

        $this->assertIsArray($comment->reactions);
        $this->assertEquals($reactions, $comment->reactions);
    }

    public function test_task_comment_uses_soft_deletes(): void
    {
        $comment = TaskComment::factory()->create();
        $comment->delete();

        $this->assertSoftDeleted('task_comments', [
            'id' => $comment->id,
        ]);

        $this->assertNull(TaskComment::find($comment->id));
        $this->assertNotNull(TaskComment::withTrashed()->find($comment->id));
    }

    public function test_task_has_comments_relationship(): void
    {
        $task = Task::factory()->create();
        $comment1 = TaskComment::factory()->create(['task_id' => $task->id]);
        $comment2 = TaskComment::factory()->create(['task_id' => $task->id]);

        $this->assertCount(2, $task->comments);
        $this->assertTrue($task->comments->contains($comment1));
        $this->assertTrue($task->comments->contains($comment2));
    }

    public function test_task_comments_are_ordered_chronologically(): void
    {
        $task = Task::factory()->create();
        
        $comment1 = TaskComment::factory()->create(['task_id' => $task->id]);
        sleep(1); // Garantir diferença de timestamp
        $comment2 = TaskComment::factory()->create(['task_id' => $task->id]);
        sleep(1);
        $comment3 = TaskComment::factory()->create(['task_id' => $task->id]);

        $comments = $task->comments()->orderBy('created_at')->get();

        $this->assertCount(3, $comments);
        $this->assertEquals($comment1->id, $comments[0]->id);
        $this->assertEquals($comment2->id, $comments[1]->id);
        $this->assertEquals($comment3->id, $comments[2]->id);
    }
}
