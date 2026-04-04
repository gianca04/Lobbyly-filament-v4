<?php

declare(strict_types=1);

namespace App\Actions;

use App\DTOs\TransferMovementData;
use App\Enums\MovementType;
use App\Models\Movement;
use App\Services\InventoryService;
use Illuminate\Support\Facades\DB;

/**
 * Acción para procesar una transferencia de artículos entre ubicaciones.
 *
 * Genera dos registros Movement independientes: uno de salida en la
 * ubicación de origen y otro de ingreso en la ubicación de destino.
 * Ambos se crean en una sola transacción atómica.
 *
 * Ejemplo de uso: Transferir 48 unidades del estante B5 al estante A4.
 * Genera: Movement(OUTPUT, B5, 48) + Movement(INPUT, A4, 48).
 */
class ProcessTransferMovementAction
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
     * Ejecuta la transferencia entre ubicaciones.
     *
     * Valida stock suficiente en origen, crea el movimiento de salida,
     * decrementa stock en origen, crea movimiento de ingreso e
     * incrementa stock en destino.
     *
     * @param  TransferMovementData  $data  DTO con origen, destino, artículo y cantidad.
     * @return array{output: Movement, input: Movement} Los dos movimientos creados.
     *
     * @throws \DomainException Si no hay stock suficiente en la ubicación de origen.
     */
    public function execute(TransferMovementData $data): array
    {
        return DB::transaction(function () use ($data): array {
            $transferNote = $data->notes
                ? "[Transferencia] {$data->notes}"
                : '[Transferencia]';

            /** Movimiento de salida desde la ubicación de origen */
            $this->inventoryService->validateSufficientStock(
                $data->itemId,
                $data->originLocationId,
                $data->quantity,
            );

            $outputMovement = Movement::create([
                'item_id' => $data->itemId,
                'location_id' => $data->originLocationId,
                'user_id' => $data->userId,
                'type' => MovementType::TRANSFER,
                'quantity' => $data->quantity,
                'notes' => $transferNote.' — Salida desde origen',
            ]);

            $this->inventoryService->decreaseStockAtLocation(
                $data->itemId,
                $data->originLocationId,
                $data->quantity,
            );

            /** Movimiento de ingreso en la ubicación de destino */
            $inputMovement = Movement::create([
                'item_id' => $data->itemId,
                'location_id' => $data->destinationLocationId,
                'user_id' => $data->userId,
                'type' => MovementType::TRANSFER,
                'quantity' => $data->quantity,
                'notes' => $transferNote.' — Ingreso en destino',
            ]);

            $this->inventoryService->increaseStockAtLocation(
                $data->itemId,
                $data->destinationLocationId,
                $data->quantity,
            );

            return [
                'output' => $outputMovement,
                'input' => $inputMovement,
            ];
        });
    }
}
