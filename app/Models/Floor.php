<?php

namespace App\Models;

use Database\Factories\FloorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Modelo que representa un piso en el sistema.
 *
 * @property int $id Identificador único del piso.
 * @property string $name Nombre del piso.
 * @property string|null $description Descripción del piso.
 * @property Carbon|null $created_at Fecha de creación.
 * @property Carbon|null $updated_at Fecha de última actualización.
 */
class Floor extends Model
{
    /** @use HasFactory<FloorFactory> */
    use HasFactory;

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            //
        ];
    }
}
