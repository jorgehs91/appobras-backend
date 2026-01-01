<?php

namespace Database\Factories;

use App\Models\Contract;
use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkOrder>
 */
class WorkOrderFactory extends Factory
{
    protected $model = WorkOrder::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'contract_id' => Contract::factory(),
            'description' => fake()->sentence(),
            'value' => fake()->randomFloat(2, 100, 10000),
            'due_date' => fake()->optional()->date(),
        ];
    }
}
