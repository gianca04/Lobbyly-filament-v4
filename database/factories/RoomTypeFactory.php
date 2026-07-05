<?php

namespace Database\Factories;

use App\Models\RoomType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoomType>
 */
class RoomTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Tipo '.$this->faker->unique()->word().' '.$this->faker->unique()->numberBetween(1, 1000),
            'base_price' => $this->faker->randomFloat(2, 40, 600),
            'is_active' => true,
            'capacity' => $this->faker->numberBetween(1, 6),
        ];
    }
}
