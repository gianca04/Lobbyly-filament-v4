<?php

namespace Database\Factories;

use App\Models\Feature;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Feature>
 */
class FeatureFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Característica '.$this->faker->unique()->numberBetween(1, 1000),
            'price' => $this->faker->randomFloat(2, 1, 500),
            'is_active' => true,
            'is_removable' => true,
        ];
    }
}
