<?php

namespace App\Models;

use Database\Factories\RoomTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * Modelo que representa un tipo de habitación en el sistema.
 *
 * @property int $id Identificador único del tipo de habitación.
 * @property string $name Nombre del tipo de habitación.
 * @property float $base_price Precio base del tipo de habitación.
 * @property bool $is_active Indica si el tipo de habitación está activo.
 * @property int $capacity Capacidad de personas del tipo de habitación.
 * @property Carbon|null $created_at Fecha de creación.
 * @property Carbon|null $updated_at Fecha de última actualización.
 * @property-read float $features_price Precio total de las características (virtual).
 * @property-read float $final_price Precio final (virtual).
 */
class RoomType extends Model
{
    /** @use HasFactory<RoomTypeFactory> */
    use HasFactory;

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'base_price',
        'is_active',
        'capacity',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'is_active' => 'boolean',
            'capacity' => 'integer',
        ];
    }

    /**
     * Obtiene las características asociadas a este tipo de habitación.
     *
     * @return BelongsToMany<Feature, RoomType>
     */
    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class, 'caracteristica_tipo_habitacion', 'habitacion_tipo_id', 'caracteristica_id')
            ->withTimestamps();
    }

    /**
     * Obtiene la suma de precios de las características asociadas.
     */
    public function getPrecioCaracteristicas(): float
    {
        return (float) $this->features()->sum('price');
    }

    /**
     * Obtiene el precio final (precio base + precio características).
     */
    public function getPrecioFinal(): float
    {
        return (float) $this->base_price + $this->getPrecioCaracteristicas();
    }

    /**
     * Accessor para el atributo virtual features_price / precio_caracteristicas.
     */
    public function getFeaturesPriceAttribute(): float
    {
        return $this->getPrecioCaracteristicas();
    }

    /**
     * Accessor para el atributo virtual final_price / precio_final.
     */
    public function getFinalPriceAttribute(): float
    {
        return $this->getPrecioFinal();
    }
}
