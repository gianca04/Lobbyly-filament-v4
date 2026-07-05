<?php

namespace App\Models;

use App\Enums\RoomStatus;
use Database\Factories\RoomFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Modelo que representa una habitación en el sistema.
 *
 * @property int $id Identificador único de la habitación.
 * @property string $number Número de la habitación.
 * @property RoomStatus $status Estado actual de la habitación.
 * @property string|null $description Descripción de la habitación.
 * @property int $floor_id Identificador del piso asociado.
 * @property int $room_type_id Identificador del tipo de habitación asociado.
 * @property string|null $notes Notas adicionales de la habitación.
 * @property string|null $location Ubicación física / detalles de acceso de la habitación.
 * @property Carbon|null $last_cleaned_at Fecha y hora de la última limpieza.
 * @property Carbon|null $created_at Fecha de creación.
 * @property Carbon|null $updated_at Fecha de última actualización.
 * @property-read Floor $floor Piso asociado.
 * @property-read RoomType $roomType Tipo de habitación asociado.
 */
class Room extends Model
{
    /** @use HasFactory<RoomFactory> */
    use HasFactory;

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var list<string>
     */
    protected $fillable = [
        'number',
        'status',
        'description',
        'floor_id',
        'room_type_id',
        'notes',
        'location',
        'last_cleaned_at',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => RoomStatus::class,
            'last_cleaned_at' => 'datetime',
            'floor_id' => 'integer',
            'room_type_id' => 'integer',
        ];
    }

    /**
     * Relación: La habitación pertenece a un piso (Floor).
     *
     * @return BelongsTo<Floor, Room>
     */
    public function floor(): BelongsTo
    {
        return $this->belongsTo(Floor::class);
    }

    /**
     * Relación: La habitación pertenece a un tipo de habitación (RoomType).
     *
     * @return BelongsTo<RoomType, Room>
     */
    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    /**
     * Obtiene el precio final de la habitación delegando en su tipo.
     */
    public function getPrecio(): float
    {
        return $this->roomType ? $this->roomType->getPrecioFinal() : 0.00;
    }

    /**
     * Relación: La habitación tiene muchos registros de cambios de estado (RoomStatusLog).
     *
     * @return HasMany<RoomStatusLog, Room>
     */
    public function statusLogs(): HasMany
    {
        return $this->hasMany(RoomStatusLog::class);
    }

    /**
     * Cambia el estado de la habitación y lo guarda, registrando la transición en el log.
     */
    public function cambiarEstado(RoomStatus|string $nuevoEstado, ?int $usuarioId = null, ?string $nota = null): bool
    {
        $this->status = $nuevoEstado instanceof RoomStatus ? $nuevoEstado : RoomStatus::from($nuevoEstado);

        if ($this->save()) {
            RoomStatusLog::create([
                'room_id' => $this->id,
                'user_id' => $usuarioId,
                'status' => $this->status,
                'note' => $nota,
            ]);

            return true;
        }

        return false;
    }

    /**
     * Obtiene las características removibles de este tipo de habitación.
     *
     * @return Collection<int, Feature>
     */
    public function getCaracteristicasRemovibles(): Collection
    {
        return $this->roomType
            ? $this->roomType->features()->where('is_removable', true)->get()
            : new Collection;
    }
}
