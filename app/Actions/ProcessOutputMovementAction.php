<?php

declare(strict_types=1);

namespace App\Actions;

use App\DTOs\MovementData;
use App\Enums\MovementType;
use App\Models\Movement;
use App\Services\InventoryService;
use Illuminate\Support\Facades\DB;

/**
 * Acción para procesar una salida de artículos del inventario.
 *
 * Valida que exista stock suficiente en la ubicación indicada,
 * registra el movimiento de salida y decrementa el stock.
 *
 * Ejemplo de uso: Se extrajeron 48 unidades del estante B5.
 */
class ProcessOutputMovementAction
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
     * Ejecuta la salida de artículos del inventario.
     *
     * Primero valida stock suficiente, luego crea el movimiento
     * y reduce el stock en la ubicación.
     *
     * @param  MovementData  $data  DTO con artículo, ubicación, cantidad y notas.
     * @return Movement El movimiento de salida creado.
     *
     * @throws \DomainException Si no hay stock suficiente en la ubicación.
     */
    public function execute(MovementData $data): Movement
    {
        return DB::transaction(function () use ($data): Movement {
            /** Validamos antes de crear el movimiento para evitar registros huérfanos */
            $this->inventoryService->validateSufficientStock(
                $data->itemId,
                $data->locationId,
                $data->quantity,
            );

            $movement = Movement::create([
                'item_id' => $data->itemId,
                'location_id' => $data->locationId,
                'user_id' => $data->userId,
                'type' => MovementType::OUTPUT,
                'quantity' => $data->quantity,
                'notes' => $data->notes,
            ]);

            $this->inventoryService->decreaseStockAtLocation(
                $data->itemId,
                $data->locationId,
                $data->quantity,
            );

            return $movement;
        });
    }
}
