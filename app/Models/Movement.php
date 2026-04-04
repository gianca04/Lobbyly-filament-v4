<?php

namespace App\Models;

use App\Enums\MovementType;
use Database\Factories\MovementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Modelo que representa un movimiento de inventario (entrada, salida, ajuste, etc.).
 *
 * @property int $id Identificador único del movimiento.
 * @property int $item_id Identificador del artículo asociado.
 * @property int $location_id Identificador de la ubicación asociada.
 * @property int $user_id Identificador del usuario que registró el movimiento.
 * @property MovementType $type Tipo de movimiento (Enum).
 * @property float $quantity Cantidad movida.
 * @property string|null $notes Notas u observaciones adicionales.
 * @property Carbon|null $created_at Fecha de registro.
 * @property Carbon|null $updated_at Fecha de última actualización.
 * @property-read Item $item Artículo relacionado.
 * @property-read Location $location Ubicación relacionada.
 * @property-read User $user Usuario responsable.
 */
class Movement extends Model
{
    /** @use HasFactory<MovementFactory> */
    use HasFactory;

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var list<string>
     */
    protected $fillable = [
        'item_id',
        'location_id',
        'user_id',
        'type',
        'quantity',
        'notes',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => MovementType::class,
            'quantity' => 'decimal:2',
        ];
    }

    /**
     * Obtiene el artículo asociado al movimiento.
     *
     * @return BelongsTo<Item, Movement>
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Obtiene la ubicación asociada al movimiento.
     *
     * @return BelongsTo<Location, Movement>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * Obtiene el usuario que registró el movimiento.
     *
     * @return BelongsTo<User, Movement>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
