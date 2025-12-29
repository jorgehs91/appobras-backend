<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'notifiable_id' => 1, // Will be overridden by states or manually set
            'notifiable_type' => Task::class, // Default, can be overridden
            'type' => fake()->randomElement([
                'task_assigned',
                'task_overdue',
                'task_completed',
                'project_updated',
                'phase_completed',
                'document_uploaded',
            ]),
            'data' => [
                'title' => fake()->sentence(),
                'message' => fake()->paragraph(),
                'action_url' => fake()->url(),
            ],
            'read_at' => null,
            'channels' => fake()->randomElements(['email', 'push', 'database'], fake()->numberBetween(1, 3)),
        ];
    }

    /**
     * Indicate that the notification is read.
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes): array => [
            'read_at' => fake()->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Indicate that the notification is unread.
     */
    public function unread(): static
    {
        return $this->state(fn (array $attributes): array => [
            'read_at' => null,
        ]);
    }

    /**
     * Set notification type to email only.
     */
    public function email(): static
    {
        return $this->state(fn (array $attributes): array => [
            'channels' => ['email'],
        ]);
    }

    /**
     * Set notification type to push only.
     */
    public function push(): static
    {
        return $this->state(fn (array $attributes): array => [
            'channels' => ['push'],
        ]);
    }

    /**
     * Set notification type to both email and push.
     */
    public function emailAndPush(): static
    {
        return $this->state(fn (array $attributes): array => [
            'channels' => ['email', 'push'],
        ]);
    }

    /**
     * Set notification type to task_assigned.
     */
    public function taskAssigned(): static
    {
        return $this->state(function (array $attributes): array {
            $task = Task::factory()->create();

            return [
                'notifiable_id' => $task->id,
                'notifiable_type' => Task::class,
                'type' => 'task_assigned',
                'data' => [
                    'task_id' => $task->id,
                    'task_title' => $task->title,
                    'assigner_name' => fake()->name(),
                ],
            ];
        });
    }

    /**
     * Set notification type to task_overdue.
     */
    public function taskOverdue(): static
    {
        return $this->state(function (array $attributes): array {
            $task = Task::factory()->create();

            return [
                'notifiable_id' => $task->id,
                'notifiable_type' => Task::class,
                'type' => 'task_overdue',
                'data' => [
                    'task_id' => $task->id,
                    'task_title' => $task->title,
                    'due_date' => fake()->dateTimeBetween('-1 week', 'now')->format('Y-m-d'),
                ],
            ];
        });
    }

    /**
     * Set notification type to project_updated.
     */
    public function projectUpdated(): static
    {
        return $this->state(function (array $attributes): array {
            $project = Project::factory()->create();

            return [
                'notifiable_id' => $project->id,
                'notifiable_type' => Project::class,
                'type' => 'project_updated',
                'data' => [
                    'project_id' => $project->id,
                    'project_name' => $project->name,
                    'updated_fields' => fake()->randomElements(['status', 'budget', 'dates'], fake()->numberBetween(1, 3)),
                ],
            ];
        });
    }
}
