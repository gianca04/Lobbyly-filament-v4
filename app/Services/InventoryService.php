<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Item;
use App\Models\ItemLocation;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Servicio centralizado para todas las operaciones de stock.
 *
 * Encapsula la lógica matemática de manejo de inventario: incrementos,
 * decrementos, ajustes y recálculos. Los Actions delegan aquí toda
 * la manipulación de cantidades para garantizar consistencia.
 *
 * Principio: este servicio NUNCA crea registros de Movement.
 * Solo gestiona las tablas `item_location` e `items.current_stock`.
 */
class InventoryService
{
    /**
     * Incrementa el stock de un artículo en una ubicación específica.
     *
     * Si la relación item-location no existe, la crea con la cantidad indicada.
     * Siempre actualiza el stock total del artículo.
     *
     * @param  int  $itemId  Identificador del artículo.
     * @param  int  $locationId  Identificador de la ubicación.
     * @param  float  $quantity  Cantidad a incrementar (debe ser positiva).
     *
     * @throws \InvalidArgumentException Si la cantidad es menor o igual a cero.
     */
    public function increaseStockAtLocation(int $itemId, int $locationId, float $quantity): void
    {
        $this->validatePositiveQuantity($quantity);

        $pivot = $this->findOrCreatePivot($itemId, $locationId);
        $pivot->increment('quantity', $quantity);

        $this->recalculateTotalStock($itemId);
    }

    /**
     * Reduce el stock de un artículo en una ubicación específica.
     *
     * Valida que exista stock suficiente antes de decrementar.
     * Siempre actualiza el stock total del artículo.
     *
     * @param  int  $itemId  Identificador del artículo.
     * @param  int  $locationId  Identificador de la ubicación.
     * @param  float  $quantity  Cantidad a reducir (debe ser positiva).
     *
     * @throws \InvalidArgumentException Si la cantidad es menor o igual a cero.
     * @throws \DomainException Si no hay stock suficiente en la ubicación.
     */
    public function decreaseStockAtLocation(int $itemId, int $locationId, float $quantity): void
    {
        $this->validatePositiveQuantity($quantity);
        $this->validateSufficientStock($itemId, $locationId, $quantity);

        $pivot = $this->findPivotOrFail($itemId, $locationId);
        $pivot->decrement('quantity', $quantity);

        $this->recalculateTotalStock($itemId);
    }

    /**
     * Establece el stock exacto de un artículo en una ubicación.
     *
     * Usado en ajustes de inventario cuando se detecta discrepancia
     * entre el stock registrado y el conteo físico real.
     *
     * @param  int  $itemId  Identificador del artículo.
     * @param  int  $locationId  Identificador de la ubicación.
     * @param  float  $newQuantity  Nueva cantidad real contabilizada.
     * @return float La diferencia entre la cantidad nueva y la anterior (puede ser negativa).
     *
     * @throws \InvalidArgumentException Si la nueva cantidad es negativa.
     */
    public function setStockAtLocation(int $itemId, int $locationId, float $newQuantity): float
    {
        if ($newQuantity < 0) {
            throw new \InvalidArgumentException('La cantidad de stock no puede ser negativa.');
        }

        $pivot = $this->findOrCreatePivot($itemId, $locationId);
        $previousQuantity = (float) $pivot->quantity;

        $pivot->update(['quantity' => $newQuantity]);

        $this->recalculateTotalStock($itemId);

        return $newQuantity - $previousQuantity;
    }

    /**
     * Recalcula el stock total del artículo sumando todas sus ubicaciones.
     *
     * Esta es la fuente de verdad: `item.current_stock` debe ser siempre
     * la suma de todos los `item_location.quantity` asociados.
     *
     * @param  int  $itemId  Identificador del artículo a recalcular.
     */
    public function recalculateTotalStock(int $itemId): void
    {
        $item = Item::findOrFail($itemId);

        $totalStock = ItemLocation::where('item_id', $itemId)->sum('quantity');

        $item->update(['current_stock' => $totalStock]);
    }

    /**
     * Obtiene el stock actual de un artículo en una ubicación específica.
     *
     * @param  int  $itemId  Identificador del artículo.
     * @param  int  $locationId  Identificador de la ubicación.
     * @return float Cantidad de stock en la ubicación (0 si no existe la relación).
     */
    public function getStockAtLocation(int $itemId, int $locationId): float
    {
        $pivot = ItemLocation::where('item_id', $itemId)
            ->where('location_id', $locationId)
            ->first();

        return $pivot ? (float) $pivot->quantity : 0.0;
    }

    /**
     * Valida que exista stock suficiente en una ubicación para la cantidad requerida.
     *
     * @param  int  $itemId  Identificador del artículo.
     * @param  int  $locationId  Identificador de la ubicación.
     * @param  float  $requiredQuantity  Cantidad mínima necesaria.
     *
     * @throws \DomainException Si el stock disponible es menor a la cantidad requerida.
     */
    public function validateSufficientStock(int $itemId, int $locationId, float $requiredQuantity): void
    {
        $currentStock = $this->getStockAtLocation($itemId, $locationId);

        if ($currentStock < $requiredQuantity) {
            $item = Item::findOrFail($itemId);

            throw new \DomainException(
                "Stock insuficiente para '{$item->name}'. "
                ."Disponible: {$currentStock}, requerido: {$requiredQuantity}."
            );
        }
    }

    /**
     * Busca el registro pivot item-location o lo crea con cantidad 0.
     *
     * @param  int  $itemId  Identificador del artículo.
     * @param  int  $locationId  Identificador de la ubicación.
     */
    private function findOrCreatePivot(int $itemId, int $locationId): ItemLocation
    {
        return ItemLocation::firstOrCreate(
            ['item_id' => $itemId, 'location_id' => $locationId],
            ['quantity' => 0],
        );
    }

    /**
     * Busca el registro pivot item-location o falla si no existe.
     *
     * @param  int  $itemId  Identificador del artículo.
     * @param  int  $locationId  Identificador de la ubicación.
     *
     * @throws ModelNotFoundException Si no existe stock registrado en esa ubicación.
     */
    private function findPivotOrFail(int $itemId, int $locationId): ItemLocation
    {
        $pivot = ItemLocation::where('item_id', $itemId)
            ->where('location_id', $locationId)
            ->first();

        if (! $pivot) {
            throw new ModelNotFoundException(
                "No existe registro de stock para el artículo #{$itemId} en la ubicación #{$locationId}."
            );
        }

        return $pivot;
    }

    /**
     * Valida que una cantidad sea estrictamente positiva.
     *
     * @param  float  $quantity  Cantidad a validar.
     *
     * @throws \InvalidArgumentException Si la cantidad es menor o igual a cero.
     */
    private function validatePositiveQuantity(float $quantity): void
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException(
                "La cantidad debe ser mayor a cero. Valor recibido: {$quantity}."
            );
        }
    }
}
