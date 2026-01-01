<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\Contract;
use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = \App\Models\Payment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'payable_type' => WorkOrder::class,
            'payable_id' => WorkOrder::factory(),
            'amount' => fake()->randomFloat(2, 100, 10000),
            'due_date' => fake()->date(),
            'status' => PaymentStatus::pending->value,
            'paid_at' => null,
            'payment_proof_path' => null,
        ];
    }

    /**
     * Indicate that the payment is for a WorkOrder.
     */
    public function forWorkOrder(?WorkOrder $workOrder = null): static
    {
        return $this->state(fn (array $attributes) => [
            'payable_type' => WorkOrder::class,
            'payable_id' => $workOrder?->id ?? WorkOrder::factory(),
        ]);
    }

    /**
     * Indicate that the payment is for a Contract.
     */
    public function forContract(?Contract $contract = null): static
    {
        return $this->state(fn (array $attributes) => [
            'payable_type' => Contract::class,
            'payable_id' => $contract?->id ?? Contract::factory(),
        ]);
    }

    /**
     * Indicate that the payment is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::pending->value,
            'paid_at' => null,
        ]);
    }

    /**
     * Indicate that the payment is paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::paid->value,
            'paid_at' => fake()->dateTime(),
        ]);
    }

    /**
     * Indicate that the payment is canceled.
     */
    public function canceled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::canceled->value,
            'paid_at' => null,
        ]);
    }

    /**
     * Indicate that the payment is overdue.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::overdue->value,
            'due_date' => fake()->dateTimeBetween('-1 month', '-1 day')->format('Y-m-d'),
            'paid_at' => null,
        ]);
    }
}
