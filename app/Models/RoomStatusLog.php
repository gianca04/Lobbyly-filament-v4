<?php

namespace App\Models;

use App\Enums\RoomStatus;
use Database\Factories\RoomStatusLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Modelo que representa el historial de cambios de estado de una habitación.
 *
 * @property int $id Identificador único del registro de log.
 * @property int $room_id Identificador de la habitación asociada.
 * @property int|null $user_id Identificador del usuario que realizó el cambio (puede ser nulo).
 * @property RoomStatus $status Estado al que cambió la habitación.
 * @property string|null $note Nota o justificación sobre el cambio de estado.
 * @property Carbon|null $created_at Fecha de creación (momento del cambio).
 * @property Carbon|null $updated_at Fecha de última actualización.
 * @property-read Room $room Habitación asociada.
 * @property-read User|null $user Usuario que realizó el cambio.
 */
class RoomStatusLog extends Model
{
    /** @use HasFactory<RoomStatusLogFactory> */
    use HasFactory;

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var list<string>
     */
    protected $fillable = [
        'room_id',
        'user_id',
        'status',
        'note',
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
            'room_id' => 'integer',
            'user_id' => 'integer',
        ];
    }

    /**
     * Relación: El log pertenece a una habitación (Room).
     *
     * @return BelongsTo<Room, RoomStatusLog>
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Relación: El log pertenece a un usuario (User).
     *
     * @return BelongsTo<User, RoomStatusLog>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
