<?php

declare(strict_types=1);

namespace App\DTOs;

/**
 * DTO para transportar datos de un ajuste de inventario.
 *
 * Un ajuste ocurre cuando se recontabiliza el stock real y se detecta
 * una diferencia con el stock registrado. La diferencia puede ser
 * positiva (sobrante) o negativa (faltante).
 *
 * @property-read int $itemId Identificador del artículo a ajustar.
 * @property-read int $userId Identificador del usuario responsable.
 * @property-read int $locationId Ubicación donde se realizó el conteo.
 * @property-read float $newQuantity Cantidad real contabilizada en la ubicación.
 * @property-read string|null $notes Justificación del ajuste (motivo del desfase).
 */
readonly class AdjustmentMovementData
{
    /**
     * Crea una nueva instancia del DTO de ajuste de inventario.
     *
     * @param  int  $itemId  Identificador del artículo a ajustar.
     * @param  int  $userId  Identificador del usuario responsable.
     * @param  int  $locationId  Ubicación donde se realizó el conteo.
     * @param  float  $newQuantity  Cantidad real contabilizada.
     * @param  string|null  $notes  Justificación del ajuste.
     */
    public function __construct(
        public int $itemId,
        public int $userId,
        public int $locationId,
        public float $newQuantity,
        public ?string $notes = null,
    ) {}
}
