<?php

namespace Database\Seeders;

use App\Models\Floor;
use Illuminate\Database\Seeder;

class FloorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $floors = [
            ['name' => '100', 'description' => 'PRIMER PISO'],
            ['name' => '200', 'description' => 'SEGUNDO PISO'],
            ['name' => '300', 'description' => 'TERCER PISO'],
            ['name' => '400', 'description' => 'CUARTO PISO'],
        ];

        foreach ($floors as $floor) {
            Floor::firstOrCreate(
                ['name' => $floor['name']],
                ['description' => $floor['description']]
            );
        }
    }
}
