<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'status' => 'in_progress',
            'start_date' => now()->subDays(30)->toDateString(),
            'end_date' => now()->addDays(60)->toDateString(),
            'planned_budget_amount' => fake()->randomFloat(2, 10000, 500000),
        ];
    }
}

