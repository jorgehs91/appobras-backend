<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Supplier>
 */
class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generate a valid CNPJ (14 digits)
        $cnpj = fake()->numerify('##############');

        return [
            'name' => fake()->company(),
            'cnpj' => $cnpj,
            'contact' => fake()->optional()->phoneNumber(),
        ];
    }
}
