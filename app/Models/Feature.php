<?php

namespace App\Models;

use Database\Factories\FeatureFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Modelo que representa una característica en el sistema.
 *
 * @property int $id Identificador único de la característica.
 * @property string $name Nombre de la característica.
 * @property float $price Precio de la característica.
 * @property bool $is_active Indica si la característica está activa.
 * @property bool $is_removable Indica si la característica es removible.
 * @property Carbon|null $created_at Fecha de creación.
 * @property Carbon|null $updated_at Fecha de última actualización.
 */
class Feature extends Model
{
    /** @use HasFactory<FeatureFactory> */
    use HasFactory;

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'price',
        'is_active',
        'is_removable',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'is_removable' => 'boolean',
        ];
    }

    /**
     * Scope para filtrar solo características activas.
     *
     * @param  Builder<Feature>  $query
     * @return Builder<Feature>
     */
    public function scopeActivas(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para filtrar solo características activas (en inglés).
     *
     * @param  Builder<Feature>  $query
     * @return Builder<Feature>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $this->scopeActivas($query);
    }

    /**
     * Scope para filtrar solo características removibles.
     *
     * @param  Builder<Feature>  $query
     * @return Builder<Feature>
     */
    public function scopeRemovibles(Builder $query): Builder
    {
        return $query->where('is_removable', true);
    }

    /**
     * Scope para filtrar solo características removibles (en inglés).
     *
     * @param  Builder<Feature>  $query
     * @return Builder<Feature>
     */
    public function scopeRemovable(Builder $query): Builder
    {
        return $this->scopeRemovibles($query);
    }

    /**
     * Obtiene el nombre con el precio formateado.
     */
    protected function nameWithPrice(): Attribute
    {
        return Attribute::get(
            fn () => $this->price > 0 ? "{$this->name} (S/ {$this->price})" : $this->name
        );
    }
}
