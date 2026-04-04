<?php

declare(strict_types=1);

namespace App\DTOs;

/**
 * DTO para transportar datos de una transferencia entre ubicaciones.
 *
 * Una transferencia genera dos movimientos independientes:
 * uno de salida en el origen y otro de ingreso en el destino.
 *
 * @property-read int $itemId Identificador del artículo a transferir.
 * @property-read int $userId Identificador del usuario responsable.
 * @property-read int $originLocationId Identificador de la ubicación de origen.
 * @property-read int $destinationLocationId Identificador de la ubicación de destino.
 * @property-read float $quantity Cantidad a transferir (siempre positiva).
 * @property-read string|null $notes Observaciones de la transferencia.
 */
readonly class TransferMovementData
{
    /**
     * Crea una nueva instancia del DTO de transferencia.
     *
     * @param  int  $itemId  Identificador del artículo a transferir.
     * @param  int  $userId  Identificador del usuario responsable.
     * @param  int  $originLocationId  Ubicación de donde se extrae el stock.
     * @param  int  $destinationLocationId  Ubicación donde se deposita el stock.
     * @param  float  $quantity  Cantidad a transferir.
     * @param  string|null  $notes  Observaciones de la transferencia.
     */
    public function __construct(
        public int $itemId,
        public int $userId,
        public int $originLocationId,
        public int $destinationLocationId,
        public float $quantity,
        public ?string $notes = null,
    ) {}
}
