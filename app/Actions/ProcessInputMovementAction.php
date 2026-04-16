<?php

declare(strict_types=1);

namespace App\Actions;

use App\DTOs\InputMovementData;
use App\Enums\MovementType;
use App\Models\Movement;
use App\Services\InventoryService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Acción para procesar un ingreso masivo de artículos al inventario.
 *
 * Registra un movimiento por cada ubicación de destino y actualiza
 * el stock correspondiente. Toda la operación se ejecuta dentro
 * de una transacción para garantizar atomicidad.
 *
 * Ejemplo de uso: Se compraron 3 cajas de jabones (24 c/u = 72 unidades).
 * 24 unidades van al estante A4 y 48 al almacén B5.
 * Se generan 2 registros Movement (uno por ubicación).
 */
class ProcessInputMovementAction
{
    /**
     * Inyecta el servicio de inventario para gestionar stock.
     *
     * @param  InventoryService  $inventoryService  Servicio centralizado de stock.
     */
    public function __construct(
        private InventoryService $inventoryService,
    ) {}

    /**
     * Ejecuta el ingreso masivo de artículos.
     *
     * Crea un registro Movement por cada distribución y actualiza
     * el stock en cada ubicación correspondiente.
     *
     * @param  InputMovementData  $data  DTO con artículo, usuario y distribuciones por ubicación.
     * @return Collection<int, Movement> Colección de movimientos creados.
     *
     * @throws \InvalidArgumentException Si alguna distribución tiene cantidad inválida.
     */
    public function execute(InputMovementData $data): Collection
    {
        return DB::transaction(function () use ($data): Collection {
            $movements = collect();

            foreach ($data->items as $itemData) {
                $movement = Movement::create([
                    'item_id' => $itemData['item_id'],
                    'location_id' => $itemData['location_id'],
                    'user_id' => $data->userId,
                    'type' => MovementType::INPUT,
                    'quantity' => $itemData['quantity'],
                    'notes' => $data->notes,
                ]);

                $this->inventoryService->increaseStockAtLocation(
                    $itemData['item_id'],
                    $itemData['location_id'],
                    (float) $itemData['quantity'],
                );

                $movements->push($movement);
            }

            return $movements;
        });
    }
}
