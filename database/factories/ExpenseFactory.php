<?php

namespace Database\Factories;

use App\Enums\ExpenseStatus;
use App\Models\CostItem;
use App\Models\Expense;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Expense>
 */
class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cost_item_id' => null,
            'project_id' => Project::factory(),
            'amount' => fake()->randomFloat(2, 10, 10000),
            'date' => fake()->date(),
            'description' => fake()->sentence(),
            'receipt_path' => fake()->optional()->filePath(),
            'status' => fake()->randomElement([
                ExpenseStatus::draft->value,
                ExpenseStatus::approved->value,
            ]),
        ];
    }

    /**
     * Indicate that the expense has a cost item.
     */
    public function withCostItem(): static
    {
        return $this->state(fn (array $attributes) => [
            'cost_item_id' => CostItem::factory(),
        ]);
    }

    /**
     * Indicate that the expense is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ExpenseStatus::draft->value,
        ]);
    }

    /**
     * Indicate that the expense is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ExpenseStatus::approved->value,
            'receipt_path' => fake()->filePath(), // Approved expenses must have receipt
        ]);
    }

    /**
     * Indicate that the expense has a receipt.
     */
    public function withReceipt(): static
    {
        return $this->state(fn (array $attributes) => [
            'receipt_path' => 'expenses/' . fake()->uuid() . '.pdf',
        ]);
    }
}
