<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\Supplier;
use App\Models\UnitOfMeasure;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory para el modelo Item.
 *
 * @extends Factory<Item>
 */
class ItemFactory extends Factory
{
    /**
     * Define el estado predeterminado del modelo.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'sku' => $this->faker->unique()->bothify('??-####'),
            'supplier_id' => Supplier::factory(),
            'unit_of_measure_id' => UnitOfMeasure::factory(),
            'unit_cost' => $this->faker->randomFloat(2, 1, 500),
            'current_stock' => $this->faker->randomFloat(2, 0, 1000),
            'minimum_stock' => $this->faker->randomFloat(2, 10, 50),
        ];
    }
}
