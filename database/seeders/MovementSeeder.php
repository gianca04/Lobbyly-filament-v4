<?php

namespace Database\Seeders;

use App\Enums\MovementType;
use App\Models\Item;
use App\Models\Location;
use App\Models\Movement;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeder para poblar la tabla de movimientos con datos iniciales.
 */
class MovementSeeder extends Seeder
{
    /**
     * Ejecuta el seeder.
     */
    public function run(): void
    {
        $item = Item::first() ?? Item::factory()->create();
        $location = Location::first() ?? Location::factory()->create();
        $user = User::first() ?? User::factory()->create();

        $movements = [
            [
                'item_id' => $item->id,
                'location_id' => $location->id,
                'user_id' => $user->id,
                'type' => MovementType::INPUT,
                'quantity' => 100,
                'notes' => 'Carga inicial por inventario.',
            ],
            [
                'item_id' => $item->id,
                'location_id' => $location->id,
                'user_id' => $user->id,
                'type' => MovementType::OUTPUT,
                'quantity' => 10,
                'notes' => 'Salida por uso en limpieza.',
            ],
            [
                'item_id' => $item->id,
                'location_id' => $location->id,
                'user_id' => $user->id,
                'type' => MovementType::ADJUSTMENT,
                'quantity' => -2,
                'notes' => 'Corredcción por artículo dañado.',
            ],
        ];

        foreach ($movements as $movement) {
            Movement::create($movement);
        }
    }
}
