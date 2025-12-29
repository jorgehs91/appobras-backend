<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AuditLog>
 */
class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'auditable_id' => Project::factory(),
            'auditable_type' => Project::class,
            'event' => fake()->randomElement(['created', 'updated', 'deleted', 'restored']),
            'old_values' => null,
            'new_values' => ['name' => fake()->words(3, true)],
            'ip' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ];
    }
}
