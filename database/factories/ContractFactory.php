<?php

namespace Database\Factories;

use App\Enums\ContractStatus;
use App\Models\Contract;
use App\Models\Contractor;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contract>
 */
class ContractFactory extends Factory
{
    protected $model = Contract::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'contractor_id' => Contractor::factory(),
            'project_id' => Project::factory(),
            'value' => fake()->randomFloat(2, 1000, 100000),
            'start_date' => fake()->date(),
            'end_date' => fake()->optional()->date(),
            'status' => fake()->randomElement([
                ContractStatus::draft->value,
                ContractStatus::active->value,
                ContractStatus::completed->value,
            ]),
        ];
    }

    /**
     * Indicate that the contract is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ContractStatus::draft->value,
        ]);
    }

    /**
     * Indicate that the contract is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ContractStatus::active->value,
        ]);
    }

    /**
     * Indicate that the contract is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ContractStatus::completed->value,
        ]);
    }

    /**
     * Indicate that the contract is canceled.
     */
    public function canceled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ContractStatus::canceled->value,
        ]);
    }
}
