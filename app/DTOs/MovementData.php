<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\MovementType;

/**
 * DTO base para transportar datos de un movimiento simple de inventario.
 *
 * Representa la información mínima necesaria para registrar una salida
 * o cualquier movimiento individual que afecte una sola ubicación.
 *
 * @property-read int $itemId Identificador del artículo afectado.
 * @property-read int $locationId Identificador de la ubicación afectada.
 * @property-read int $userId Identificador del usuario responsable.
 * @property-read MovementType $type Tipo de movimiento a registrar.
 * @property-read float $quantity Cantidad a mover (siempre positiva).
 * @property-read string|null $notes Observaciones opcionales del movimiento.
 */
readonly class MovementData
{
    /**
     * Crea una nueva instancia del DTO de movimiento.
     *
     * @param  int  $itemId  Identificador del artículo afectado.
     * @param  int  $locationId  Identificador de la ubicación afectada.
     * @param  int  $userId  Identificador del usuario responsable.
     * @param  MovementType  $type  Tipo de movimiento a registrar.
     * @param  float  $quantity  Cantidad a mover (siempre positiva).
     * @param  string|null  $notes  Observaciones opcionales del movimiento.
     */
    public function __construct(
        public int $itemId,
        public int $locationId,
        public int $userId,
        public MovementType $type,
        public float $quantity,
        public ?string $notes = null,
    ) {}
}
