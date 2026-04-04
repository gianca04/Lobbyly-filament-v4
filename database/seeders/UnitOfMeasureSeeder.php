<?php

namespace Database\Seeders;

use App\Models\UnitOfMeasure;
use Illuminate\Database\Seeder;

class UnitOfMeasureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            ['name' => 'Unidad', 'symbol' => 'und'],
            ['name' => 'Kilogramo', 'symbol' => 'kg'],
            ['name' => 'Metro', 'symbol' => 'm'],
            ['name' => 'Litro', 'symbol' => 'L'],
            ['name' => 'Paquete', 'symbol' => 'paq'],
        ];

        foreach ($units as $unit) {
            UnitOfMeasure::firstOrCreate(
                ['symbol' => $unit['symbol']],
                ['name' => $unit['name']]
            );
        }
    }
}
