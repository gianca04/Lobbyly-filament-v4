<?php

namespace Database\Seeders;

use App\Models\Feature;
use Illuminate\Database\Seeder;

class FeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $features = [
            ['name' => 'TV', 'price' => 0.00],
            ['name' => 'SMART TV', 'price' => 0.00],
            ['name' => 'DUCHA CALIENTE', 'price' => 0.00],
            ['name' => 'WIFI', 'price' => 0.00],
            ['name' => 'FRIGOBAR', 'price' => 20.00],
            ['name' => '1 CAMA - 1/2 PLAZA', 'price' => 0.00],
            ['name' => '2 CAMAS - 2 PLAZAS', 'price' => 0.00],
            ['name' => 'AIRE ACONDICIONADO', 'price' => 20.00],
            ['name' => '2 CAMARAS - 1/2 PLAZA', 'price' => 0.00],
        ];

        foreach ($features as $feature) {
            $isRemovable = $feature['price'] > 0;

            Feature::firstOrCreate(
                ['name' => $feature['name']],
                [
                    'price' => $feature['price'],
                    'is_active' => true,
                    'is_removable' => $isRemovable,
                ]
            );
        }
    }
}
