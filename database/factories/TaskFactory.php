<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Contractor;
use App\Models\Phase;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('now', '+1 month');
        $end = fake()->dateTimeBetween($start, '+2 months');
        $status = fake()->randomElement(['backlog', 'in_progress', 'in_review', 'done', 'canceled']);

        return [
            'company_id' => Company::factory(),
            'project_id' => Project::factory(),
            'phase_id' => Phase::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'status' => $status,
            'priority' => fake()->randomElement(['low', 'medium', 'high', 'urgent']),
            'order_in_phase' => fake()->numberBetween(0, 10),
            'assignee_id' => User::factory(),
            'contractor_id' => fake()->boolean(50) ? Contractor::factory() : null,
            'is_blocked' => false,
            'blocked_reason' => null,
            'planned_start_at' => $start,
            'planned_end_at' => $end,
            'due_at' => $end,
            'started_at' => $status === 'in_progress' || $status === 'in_review' || $status === 'done' ? now() : null,
            'completed_at' => $status === 'done' ? now() : null,
        ];
    }
}
