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
}
