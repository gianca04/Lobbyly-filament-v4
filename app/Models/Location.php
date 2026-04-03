<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Modelo que representa una ubicación o almacén en el sistema.
 *
 * @property int $id Identificador único de la ubicación.
 * @property string $name Nombre de la ubicación.
 * @property bool $is_default Indica si es la ubicación por defecto.
 * @property Carbon|null $created_at Fecha de creación.
 * @property Carbon|null $updated_at Fecha de última actualización.
 */
class Location extends Model
{
    /** @use HasFactory<\Database\Factories\LocationFactory> */
    use HasFactory;

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'is_default',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }
}
