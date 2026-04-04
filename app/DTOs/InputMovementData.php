<?php

declare(strict_types=1);

namespace App\DTOs;

/**
 * DTO para transportar datos de un ingreso masivo de inventario.
 *
 * Permite distribuir una compra en múltiples ubicaciones simultáneamente.
 * Por ejemplo: se compraron 3 cajas de 24 unidades = 72 unidades totales,
 * 24 van al estante A4 y 48 al almacén B5.
 *
 * @property-read int $itemId Identificador del artículo ingresado.
 * @property-read int $userId Identificador del usuario responsable.
 * @property-read string|null $notes Observaciones generales del ingreso.
 * @property-read array<int, array{location_id: int, quantity: float}> $distributions Distribución por ubicación.
 */
readonly class InputMovementData
{
    /**
     * Crea una nueva instancia del DTO de ingreso masivo.
     *
     * @param  int  $itemId  Identificador del artículo ingresado.
     * @param  int  $userId  Identificador del usuario responsable.
     * @param  array<int, array{location_id: int, quantity: float}>  $distributions  Distribución del ingreso por ubicación.
     * @param  string|null  $notes  Observaciones generales del ingreso.
     */
    public function __construct(
        public int $itemId,
        public int $userId,
        public array $distributions,
        public ?string $notes = null,
    ) {}

    /**
     * Calcula la cantidad total sumando todas las distribuciones.
     *
     * Útil para validar contra la cantidad esperada de la compra.
     */
    public function totalQuantity(): float
    {
        return array_sum(array_column($this->distributions, 'quantity'));
    }
}
