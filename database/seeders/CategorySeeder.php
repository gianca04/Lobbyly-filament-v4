<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Insumos de Limpieza',
            'Ropa de Cama y Toallas',
            'Amenities y Aseo Personal',
            'Insumos de Cafetería / Cocina',
            'Papelería e Insumos de Oficina',
            'Ferretería y Mantenimiento',
            'Electrónicos y Baterías',
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate([
                'name' => $category,
            ]);
        }
    }
}
