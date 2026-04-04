<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\Supplier;
use App\Models\UnitOfMeasure;
use Illuminate\Database\Seeder;

/**
 * Seeder para poblar la tabla de artículos con datos iniciales.
 */
class ItemSeeder extends Seeder
{
    /**
     * Ejecuta el seeder.
     */
    public function run(): void
    {
        $supplier = Supplier::first() ?? Supplier::factory()->create();
        $und = UnitOfMeasure::where('symbol', 'und')->first() ?? UnitOfMeasure::firstOrCreate(['name' => 'Unidad'], ['symbol' => 'und']);
        $kg = UnitOfMeasure::where('symbol', 'kg')->first() ?? UnitOfMeasure::firstOrCreate(['name' => 'Kilogramo'], ['symbol' => 'kg']);
        $lt = UnitOfMeasure::whereIn('symbol', ['lt', 'L', 'lt.'])->first() ?? UnitOfMeasure::firstOrCreate(['name' => 'Litro'], ['symbol' => 'L']);

        $items = [
            [
                'name' => 'Detergente Industrial 5L',
                'sku' => 'LIM-001',
                'supplier_id' => $supplier->id,
                'unit_of_measure_id' => $lt->id,
                'unit_cost' => 45.50,
                'current_stock' => 20,
                'minimum_stock' => 5,
            ],
            [
                'name' => 'Sábanas Algodón King Size',
                'sku' => 'TX-001',
                'supplier_id' => $supplier->id,
                'unit_of_measure_id' => $und->id,
                'unit_cost' => 120.00,
                'current_stock' => 15,
                'minimum_stock' => 2,
            ],
            [
                'name' => 'Jabón de Tocador 20g (Caja x 100)',
                'sku' => 'AMN-001',
                'supplier_id' => $supplier->id,
                'unit_of_measure_id' => $und->id,
                'unit_cost' => 85.00,
                'current_stock' => 10,
                'minimum_stock' => 2,
            ],
            [
                'name' => 'Café Tostado en Grano 1kg',
                'sku' => 'CAF-001',
                'supplier_id' => $supplier->id,
                'unit_of_measure_id' => $kg->id,
                'unit_cost' => 38.00,
                'current_stock' => 12,
                'minimum_stock' => 3,
            ],
            [
                'name' => 'Pilas Alcalinas AA (Pack x 4)',
                'sku' => 'ELE-001',
                'supplier_id' => $supplier->id,
                'unit_of_measure_id' => $und->id,
                'unit_cost' => 12.50,
                'current_stock' => 50,
                'minimum_stock' => 10,
            ],
        ];

        foreach ($items as $item) {
            Item::updateOrCreate(
                ['sku' => $item['sku']],
                $item
            );
        }
    }
}
