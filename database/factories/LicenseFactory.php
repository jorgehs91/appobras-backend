<?php

namespace Database\Factories;

use App\Models\File;
use App\Models\License;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\License>
 */
class LicenseFactory extends Factory
{
    protected $model = License::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'file_id' => File::factory(),
            'project_id' => Project::factory(),
            'expiry_date' => fake()->dateTimeBetween('now', '+1 year')->format('Y-m-d'),
            'status' => fake()->optional()->randomElement(['active', 'expired', 'pending_renewal']),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the license is active (not expired).
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'expiry_date' => fake()->dateTimeBetween('now', '+1 year')->format('Y-m-d'),
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the license is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expiry_date' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'status' => 'expired',
        ]);
    }

    /**
     * Indicate that the license is expiring soon.
     */
    public function expiringSoon(int $days = 30): static
    {
        return $this->state(fn (array $attributes) => [
            'expiry_date' => fake()->dateTimeBetween('now', "+{$days} days")->format('Y-m-d'),
            'status' => 'active',
        ]);
    }
}
