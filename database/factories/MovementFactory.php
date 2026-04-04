<?php

namespace Database\Factories;

use App\Enums\MovementType;
use App\Models\Item;
use App\Models\Location;
use App\Models\Movement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory para el modelo Movement.
 *
 * @extends Factory<Movement>
 */
class MovementFactory extends Factory
{
    /**
     * Define el estado predeterminado del modelo.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'item_id' => Item::factory(),
            'location_id' => Location::factory(),
            'user_id' => User::factory(),
            'type' => $this->faker->randomElement(MovementType::cases()),
            'quantity' => $this->faker->randomFloat(2, 1, 100),
            'notes' => $this->faker->sentence(),
        ];
    }
}
