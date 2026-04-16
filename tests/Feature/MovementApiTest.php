<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\MovementType;
use App\Models\Item;
use App\Models\ItemLocation;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests de Feature para los endpoints de movimientos de inventario.
 *
 * Cubre los 4 tipos de movimiento activos: INPUT, OUTPUT, TRANSFER, ADJUSTMENT.
 * Valida tanto los caminos felices como los casos de error.
 */
class MovementApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Item $item;

    private Location $locationA;

    private Location $locationB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->item = Item::factory()->create(['current_stock' => 0]);
        $this->locationA = Location::factory()->create(['name' => 'Estante A4']);
        $this->locationB = Location::factory()->create(['name' => 'Almacén B5']);
    }

    // ─── INPUT (Ingreso masivo) ───────────────────────────────────────

    public function test_input_movement_creates_movements_and_updates_stock(): void
    {
        $response = $this->actingAs($this->user)->postJson('/internal/movements/input', [
            'item_id' => $this->item->id,
            'distributions' => [
                ['location_id' => $this->locationA->id, 'quantity' => 24],
                ['location_id' => $this->locationB->id, 'quantity' => 48],
            ],
            'notes' => 'Compra de 3 cajas de jabones.',
        ]);

        $response->assertStatus(201);

        /** Verificar que se crearon 2 registros de movimiento */
        $this->assertDatabaseCount('movements', 2);

        $this->assertDatabaseHas('movements', [
            'item_id' => $this->item->id,
            'location_id' => $this->locationA->id,
            'type' => MovementType::INPUT->value,
            'quantity' => '24.00',
        ]);

        $this->assertDatabaseHas('movements', [
            'item_id' => $this->item->id,
            'location_id' => $this->locationB->id,
            'type' => MovementType::INPUT->value,
            'quantity' => '48.00',
        ]);

        /** Verificar stock por ubicación */
        $this->assertDatabaseHas('item_location', [
            'item_id' => $this->item->id,
            'location_id' => $this->locationA->id,
            'quantity' => '24.00',
        ]);

        $this->assertDatabaseHas('item_location', [
            'item_id' => $this->item->id,
            'location_id' => $this->locationB->id,
            'quantity' => '48.00',
        ]);

        /** Verificar stock total del artículo */
        $this->item->refresh();
        $this->assertEquals(72.00, (float) $this->item->current_stock);
    }

    public function test_input_movement_fails_with_invalid_data(): void
    {
        $response = $this->actingAs($this->user)->postJson('/internal/movements/input', [
            'item_id' => 9999,
            'distributions' => [],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['item_id', 'distributions']);
    }

    public function test_input_movement_fails_with_zero_quantity(): void
    {
        $response = $this->actingAs($this->user)->postJson('/internal/movements/input', [
            'item_id' => $this->item->id,
            'distributions' => [
                ['location_id' => $this->locationA->id, 'quantity' => 0],
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['distributions.0.quantity']);
    }

    // ─── OUTPUT (Salida) ───────────────────────────────────────────────

    public function test_output_movement_decreases_stock(): void
    {
        /** Preparar stock inicial */
        ItemLocation::create([
            'item_id' => $this->item->id,
            'location_id' => $this->locationA->id,
            'quantity' => 100,
        ]);
        $this->item->update(['current_stock' => 100]);

        $response = $this->actingAs($this->user)->postJson('/internal/movements/output', [
            'item_id' => $this->item->id,
            'location_id' => $this->locationA->id,
            'quantity' => 30,
            'notes' => 'Entrega a cliente.',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('movements', [
            'item_id' => $this->item->id,
            'location_id' => $this->locationA->id,
            'type' => MovementType::OUTPUT->value,
            'quantity' => '30.00',
        ]);

        /** Verificar que el stock se redujo */
        $this->assertDatabaseHas('item_location', [
            'item_id' => $this->item->id,
            'location_id' => $this->locationA->id,
            'quantity' => '70.00',
        ]);

        $this->item->refresh();
        $this->assertEquals(70.00, (float) $this->item->current_stock);
    }

    public function test_output_movement_fails_with_insufficient_stock(): void
    {
        ItemLocation::create([
            'item_id' => $this->item->id,
            'location_id' => $this->locationA->id,
            'quantity' => 10,
        ]);
        $this->item->update(['current_stock' => 10]);

        $response = $this->actingAs($this->user)->postJson('/internal/movements/output', [
            'item_id' => $this->item->id,
            'location_id' => $this->locationA->id,
            'quantity' => 50,
        ]);

        /** Debe fallar con error de dominio */
        $response->assertStatus(500);

        /** Stock no debe haber cambiado */
        $this->item->refresh();
        $this->assertEquals(10.00, (float) $this->item->current_stock);
    }

    public function test_output_movement_fails_with_no_stock_record(): void
    {
        $response = $this->actingAs($this->user)->postJson('/internal/movements/output', [
            'item_id' => $this->item->id,
            'location_id' => $this->locationA->id,
            'quantity' => 5,
        ]);

        $response->assertStatus(500);
    }

    // ─── TRANSFER (Transferencia) ──────────────────────────────────────

    public function test_transfer_movement_creates_two_movements(): void
    {
        /** Stock inicial en origen (B5) */
        ItemLocation::create([
            'item_id' => $this->item->id,
            'location_id' => $this->locationB->id,
            'quantity' => 100,
        ]);
        $this->item->update(['current_stock' => 100]);

        $response = $this->actingAs($this->user)->postJson('/internal/movements/transfer', [
            'item_id' => $this->item->id,
            'origin_location_id' => $this->locationB->id,
            'destination_location_id' => $this->locationA->id,
            'quantity' => 48,
            'notes' => 'Mover al estante de exhibición.',
        ]);

        $response->assertStatus(201);

        /** Deben existir 2 movimientos de transferencia */
        $this->assertDatabaseCount('movements', 2);

        /** Stock en origen reducido */
        $this->assertDatabaseHas('item_location', [
            'item_id' => $this->item->id,
            'location_id' => $this->locationB->id,
            'quantity' => '52.00',
        ]);

        /** Stock en destino incrementado */
        $this->assertDatabaseHas('item_location', [
            'item_id' => $this->item->id,
            'location_id' => $this->locationA->id,
            'quantity' => '48.00',
        ]);

        /** Stock total no cambia (solo se redistribuye) */
        $this->item->refresh();
        $this->assertEquals(100.00, (float) $this->item->current_stock);
    }

    public function test_transfer_fails_when_origin_equals_destination(): void
    {
        $response = $this->actingAs($this->user)->postJson('/internal/movements/transfer', [
            'item_id' => $this->item->id,
            'origin_location_id' => $this->locationA->id,
            'destination_location_id' => $this->locationA->id,
            'quantity' => 10,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['destination_location_id']);
    }

    public function test_transfer_fails_with_insufficient_stock_in_origin(): void
    {
        ItemLocation::create([
            'item_id' => $this->item->id,
            'location_id' => $this->locationB->id,
            'quantity' => 5,
        ]);
        $this->item->update(['current_stock' => 5]);

        $response = $this->actingAs($this->user)->postJson('/internal/movements/transfer', [
            'item_id' => $this->item->id,
            'origin_location_id' => $this->locationB->id,
            'destination_location_id' => $this->locationA->id,
            'quantity' => 50,
        ]);

        $response->assertStatus(500);

        /** No se deben haber creado movimientos (transacción rollback) */
        $this->assertDatabaseCount('movements', 0);
    }

    // ─── ADJUSTMENT (Ajuste) ───────────────────────────────────────────

    public function test_adjustment_movement_corrects_stock_downward(): void
    {
        /** Sistema dice 50, pero conteo real es 42 */
        ItemLocation::create([
            'item_id' => $this->item->id,
            'location_id' => $this->locationB->id,
            'quantity' => 50,
        ]);
        $this->item->update(['current_stock' => 50]);

        $response = $this->actingAs($this->user)->postJson('/internal/movements/adjustment', [
            'item_id' => $this->item->id,
            'location_id' => $this->locationB->id,
            'new_quantity' => 42,
            'notes' => 'Faltante detectado en reconteo.',
        ]);

        $response->assertStatus(201);

        /** Movimiento con la diferencia negativa */
        $this->assertDatabaseHas('movements', [
            'item_id' => $this->item->id,
            'location_id' => $this->locationB->id,
            'type' => MovementType::ADJUSTMENT->value,
            'quantity' => '-8.00',
        ]);

        /** Stock corregido */
        $this->assertDatabaseHas('item_location', [
            'item_id' => $this->item->id,
            'location_id' => $this->locationB->id,
            'quantity' => '42.00',
        ]);

        $this->item->refresh();
        $this->assertEquals(42.00, (float) $this->item->current_stock);
    }

    public function test_adjustment_movement_corrects_stock_upward(): void
    {
        /** Sistema dice 30, pero conteo real es 35 (sobrante) */
        ItemLocation::create([
            'item_id' => $this->item->id,
            'location_id' => $this->locationA->id,
            'quantity' => 30,
        ]);
        $this->item->update(['current_stock' => 30]);

        $response = $this->actingAs($this->user)->postJson('/internal/movements/adjustment', [
            'item_id' => $this->item->id,
            'location_id' => $this->locationA->id,
            'new_quantity' => 35,
            'notes' => 'Sobrante detectado en reconteo.',
        ]);

        $response->assertStatus(201);

        /** Movimiento con diferencia positiva */
        $this->assertDatabaseHas('movements', [
            'item_id' => $this->item->id,
            'type' => MovementType::ADJUSTMENT->value,
            'quantity' => '5.00',
        ]);

        $this->item->refresh();
        $this->assertEquals(35.00, (float) $this->item->current_stock);
    }

    public function test_adjustment_creates_pivot_if_not_exists(): void
    {
        /** No hay registro previo en item_location */
        $response = $this->actingAs($this->user)->postJson('/internal/movements/adjustment', [
            'item_id' => $this->item->id,
            'location_id' => $this->locationA->id,
            'new_quantity' => 15,
            'notes' => 'Inventario inicial detectado.',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('item_location', [
            'item_id' => $this->item->id,
            'location_id' => $this->locationA->id,
            'quantity' => '15.00',
        ]);

        $this->item->refresh();
        $this->assertEquals(15.00, (float) $this->item->current_stock);
    }

    // ─── INDEX & SHOW ──────────────────────────────────────────────────

    public function test_index_returns_paginated_movements(): void
    {
        /** Primero generar un ingreso para tener movimientos */
        $this->actingAs($this->user)->postJson('/internal/movements/input', [
            'item_id' => $this->item->id,
            'distributions' => [
                ['location_id' => $this->locationA->id, 'quantity' => 10],
            ],
        ]);

        $response = $this->actingAs($this->user)->getJson('/internal/movements');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'item_id', 'location_id', 'type', 'type_label', 'quantity', 'signed_quantity'],
            ],
        ]);
    }

    public function test_index_filters_by_type(): void
    {
        $this->actingAs($this->user)->postJson('/internal/movements/input', [
            'item_id' => $this->item->id,
            'distributions' => [
                ['location_id' => $this->locationA->id, 'quantity' => 10],
            ],
        ]);

        $response = $this->actingAs($this->user)->getJson('/internal/movements?type=input');
        $response->assertStatus(200);

        $data = $response->json('data');
        foreach ($data as $movement) {
            $this->assertEquals('input', $movement['type']);
        }
    }

    public function test_show_returns_single_movement_with_relations(): void
    {
        $this->actingAs($this->user)->postJson('/internal/movements/input', [
            'item_id' => $this->item->id,
            'distributions' => [
                ['location_id' => $this->locationA->id, 'quantity' => 5],
            ],
        ]);

        $response = $this->actingAs($this->user)->getJson('/internal/movements/1');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['id', 'item', 'location', 'user', 'type', 'quantity'],
        ]);
    }
}
