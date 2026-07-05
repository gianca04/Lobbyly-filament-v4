<?php

namespace Database\Factories;

use App\Enums\RoomStatus;
use App\Models\Room;
/**
 * @extends Factory<RoomStatusLog>
 */
use App\Models\RoomStatusLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomStatusLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'room_id' => Room::factory(),
            'user_id' => User::factory(),
            'status' => $this->faker->randomElement(RoomStatus::cases()),
            'note' => $this->faker->sentence(),
        ];
    }
}
