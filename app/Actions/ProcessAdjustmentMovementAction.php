<?php

declare(strict_types=1);

namespace App\Actions;

use App\DTOs\AdjustmentMovementData;
use App\Enums\MovementType;
use App\Models\Movement;
use App\Services\InventoryService;
use Illuminate\Support\Facades\DB;

/**
 * Acción para procesar un ajuste (reconteo) de inventario.
 *
 * Cuando el stock físico no coincide con el registrado, este ajuste
 * corrige la diferencia. Registra un Movement con la diferencia
 * (positiva si hay sobrante, negativa si hay faltante) y actualiza
 * el stock real.
 *
 * Ejemplo de uso: El sistema dice que hay 50 unidades en B5, pero
 * al contar solo hay 42. Se crea un Movement de ajuste con quantity = -8.
 */
class ProcessAdjustmentMovementAction
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
     * Ejecuta el ajuste de inventario.
     *
     * Calcula la diferencia entre el stock actual y el conteo real,
     * registra el movimiento con la diferencia y sobreescribe el stock.
     *
     * @param  AdjustmentMovementData  $data  DTO con artículo, ubicación y cantidad real contada.
     * @return Movement El movimiento de ajuste creado.
     */
    public function execute(AdjustmentMovementData $data): Movement
    {
        return DB::transaction(function () use ($data): Movement {
            /**
             * Calculamos la diferencia para registrarla como cantidad del movimiento.
             * setStockAtLocation retorna: (newQuantity - previousQuantity)
             */
            $difference = $this->inventoryService->setStockAtLocation(
                $data->itemId,
                $data->locationId,
                $data->newQuantity,
            );

            $movement = Movement::create([
                'item_id' => $data->itemId,
                'location_id' => $data->locationId,
                'user_id' => $data->userId,
                'type' => MovementType::ADJUSTMENT,
                'quantity' => $difference,
                'notes' => $data->notes ?? "Ajuste de inventario. Cantidad contabilizada: {$data->newQuantity}",
            ]);

            return $movement;
        });
    }
}
