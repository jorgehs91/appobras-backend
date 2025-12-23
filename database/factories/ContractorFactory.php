<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Contractor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contractor>
 */
class ContractorFactory extends Factory
{
    protected $model = Contractor::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'name' => fake()->company(),
            'contact' => fake()->phoneNumber(),
            'specialties' => fake()->randomElement([
                'Fundação, Estrutura',
                'Alvenaria, Acabamentos',
                'Instalações Elétricas',
                'Instalações Hidráulicas',
                'Pintura',
                'Carpintaria',
            ]),
        ];
    }
}
