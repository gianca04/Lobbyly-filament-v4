<?php

namespace Database\Factories;

use App\Enums\RoomStatus;
use App\Models\Floor;
/**
 * @extends Factory<Room>
 */
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'number' => 'Hab-'.$this->faker->unique()->numberBetween(100, 999),
            'status' => RoomStatus::AVAILABLE,
            'description' => $this->faker->sentence(),
            'floor_id' => Floor::factory(),
            'room_type_id' => RoomType::factory(),
            'notes' => $this->faker->paragraph(),
            'location' => 'Bloque '.$this->faker->randomLetter().' - Piso '.$this->faker->randomDigit(),
            'last_cleaned_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ];
    }
}
