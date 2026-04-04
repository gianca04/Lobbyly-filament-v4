<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Modelo pivot que representa la relación entre artículos y ubicaciones.
 *
 * @property int $id Identificador único.
 * @property int $item_id Identificador del artículo.
 * @property int $location_id Identificador de la ubicación.
 * @property float $quantity Cantidad de stock en esta ubicación.
 */
class ItemLocation extends Pivot
{
    /**
     * Indica si los IDs de la tabla son incrementales.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
        ];
    }
}
