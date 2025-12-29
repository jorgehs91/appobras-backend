<?php

namespace Database\Factories;

use App\Models\Budget;
use App\Models\CostItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CostItem>
 */
class CostItemFactory extends Factory
{
    protected $model = CostItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = [
            'Materiais',
            'Mão de Obra',
            'Equipamentos',
            'Serviços Terceirizados',
            'Transporte',
            'Administração',
            'Impostos',
            'Outros',
        ];

        $units = [
            'm²',
            'm³',
            'kg',
            'ton',
            'unidade',
            'hora',
            'dia',
            'mês',
            null,
        ];

        return [
            'budget_id' => Budget::factory(),
            'name' => fake()->words(3, true),
            'category' => fake()->randomElement($categories),
            'planned_amount' => fake()->randomFloat(2, 100, 50000),
            'unit' => fake()->randomElement($units),
        ];
    }
}
