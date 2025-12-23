<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Phase;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Phase>
 */
class PhaseFactory extends Factory
{
    protected $model = Phase::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('now', '+1 month');
        $end = fake()->dateTimeBetween($start, '+3 months');

        return [
            'company_id' => Company::factory(),
            'project_id' => Project::factory(),
            'name' => fake()->randomElement([
                'Planejamento e Projeto',
                'Preparação do Canteiro',
                'Fundação',
                'Estrutura',
                'Vedações e Cobertura',
                'Instalações',
                'Esquadrias e Fachada',
                'Acabamentos Internos',
                'Comissionamento e Entrega',
            ]),
            'description' => fake()->sentence(),
            'status' => fake()->randomElement(['draft', 'active', 'archived']),
            'sequence' => fake()->numberBetween(1, 10),
            'color' => fake()->hexColor(),
            'planned_start_at' => $start,
            'planned_end_at' => $end,
            'actual_start_at' => null,
            'actual_end_at' => null,
        ];
    }
}
