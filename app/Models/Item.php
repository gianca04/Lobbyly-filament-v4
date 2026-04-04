<?php

namespace App\Models;

use Database\Factories\ItemFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * Modelo que representa un artículo o producto en el inventario.
 *
 * @property int $id Identificador único del artículo.
 * @property string $name Nombre del artículo.
 * @property string|null $sku Código único de seguimiento (Stock Keeping Unit).
 * @property int $supplier_id Identificador del proveedor asociado.
 * @property int $unit_of_measure_id Identificador de la unidad de medida.
 * @property float $unit_cost Costo unitario del artículo.
 * @property float $current_stock Existencia actual en inventario.
 * @property float $minimum_stock Existencia mínima permitida antes de reordenar.
 * @property Carbon|null $created_at Fecha de creación del registro.
 * @property Carbon|null $updated_at Fecha de última actualización.
 * @property-read Supplier $supplier Proveedor del artículo.
 * @property-read UnitOfMeasure $unitOfMeasure Unidad de medida del artículo.
 * @property-read Collection<int, Location> $locations Ubicaciones donde se encuentra el artículo.
 * @property-read Collection<int, Category> $categories Categorías a las que pertenece el artículo.
 */
class Item extends Model
{
    /** @use HasFactory<ItemFactory> */
    use HasFactory;

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'sku',
        'supplier_id',
        'unit_of_measure_id',
        'unit_cost',
        'current_stock',
        'minimum_stock',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'unit_cost' => 'decimal:2',
            'current_stock' => 'decimal:2',
            'minimum_stock' => 'decimal:2',
        ];
    }

    /**
     * Obtiene el proveedor asociado al artículo.
     *
     * @return BelongsTo<Supplier, Item>
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Obtiene la unidad de medida asociada al artículo.
     *
     * @return BelongsTo<UnitOfMeasure, Item>
     */
    public function unitOfMeasure(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasure::class);
    }

    /**
     * Obtiene las ubicaciones asociadas al artículo.
     *
     * @return BelongsToMany<Location, Item>
     */
    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class)
            ->using(ItemLocation::class)
            ->withPivot('quantity')
            ->withTimestamps();
    }

    /**
     * Obtiene las categorías asociadas al artículo.
     *
     * @return BelongsToMany<Category, Item>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)
            ->using(CategoryItem::class)
            ->withTimestamps();
    }

    /**
     * Ajusta el stock actual sumando o restando un delta.
     *
     * Un delta positivo incrementa el stock, uno negativo lo reduce.
     * No valida si el resultado es negativo; eso es responsabilidad
     * del servicio que invoca este método.
     *
     * @param  float  $delta  Cantidad a sumar (positiva) o restar (negativa).
     */
    public function adjustCurrentStock(float $delta): void
    {
        $this->update([
            'current_stock' => (float) $this->current_stock + $delta,
        ]);
    }

    /**
     * Recalcula el stock total sumando el stock de todas las ubicaciones.
     *
     * Garantiza que `current_stock` sea siempre la fuente de verdad
     * derivada de la tabla pivot `item_location`.
     */
    public function recalculateCurrentStock(): void
    {
        $totalStock = $this->locations()->sum('item_location.quantity');

        $this->update(['current_stock' => $totalStock]);
    }

    /**
     * Verifica si el stock actual está por debajo del mínimo permitido.
     *
     * Útil para generar alertas de reabastecimiento.
     */
    public function isStockBelowMinimum(): bool
    {
        return (float) $this->current_stock < (float) $this->minimum_stock;
    }

    /**
     * Obtiene el stock disponible en una ubicación específica.
     *
     * @param  Location  $location  Ubicación a consultar.
     * @return float Cantidad de stock en la ubicación (0 si no existe relación).
     */
    public function getStockAtLocation(Location $location): float
    {
        $pivot = $this->locations()->where('locations.id', $location->id)->first();

        return $pivot ? (float) $pivot->pivot->quantity : 0.0;
    }
}
