<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Modelo pivot que representa la relación entre artículos y ubicaciones.
 *
 * @property int $id Identificador único.
 * @property int $item_id Identificador del artículo.
 * @property int $location_id Identificador de la ubicación.
 * @property float $quantity Cantidad de stock en esta ubicación.
 * @property-read Item $item Artículo asociado.
 * @property-read Location $location Ubicación asociada.
 */
class ItemLocation extends Pivot
{
    /**
     * Nombre de la tabla asociada al modelo pivot.
     *
     * @var string
     */
    protected $table = 'item_location';

    /**
     * Indica si los IDs de la tabla son incrementales.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var list<string>
     */
    protected $fillable = [
        'item_id',
        'location_id',
        'quantity',
    ];

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

    /**
     * Obtiene el artículo asociado.
     *
     * @return BelongsTo<Item, ItemLocation>
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Obtiene la ubicación asociada.
     *
     * @return BelongsTo<Location, ItemLocation>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
