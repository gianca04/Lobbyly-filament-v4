<?php

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * Modelo que representa una categoría de productos o equipos en el sistema.
 *
 * @property int $id Identificador único de la categoría.
 * @property string $name Nombre de la categoría.
 * @property Carbon|null $created_at Fecha de creación.
 * @property Carbon|null $updated_at Fecha de última actualización.
 * @property-read Collection<int, Item> $items Artículos en esta categoría.
 */
class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
    ];

    /**
     * Obtiene los artículos asociados a la categoría.
     *
     * @return BelongsToMany<Item, Category>
     */
    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class)
            ->using(CategoryItem::class)
            ->withTimestamps();
    }
}
