<?php

namespace App\Models;

use App\Enums\DocumentType;
use Database\Factories\EmployeeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Modelo que representa a un empleado en el sistema.
 *
 * @property int $id Identificador único del empleado.
 * @property int $user_id Identificador del usuario asociado.
 * @property DocumentType $document_type Tipo de documento de identidad.
 * @property string $document_number Número de documento de identidad.
 * @property string $first_name Nombres del empleado.
 * @property string $last_name Apellidos del empleado.
 * @property Carbon $birth_date Fecha de nacimiento.
 * @property array|null $phones Lista de números de teléfono.
 * @property array|null $addresses Lista de direcciones.
 * @property string|null $photo_path Ruta de la fotografía del empleado.
 * @property bool $is_active Indica si el empleado está activo.
 * @property Carbon|null $created_at Fecha de creación del registro.
 * @property Carbon|null $updated_at Fecha de última actualización.
 */
class Employee extends Model
{
    /** @use HasFactory<EmployeeFactory> */
    use HasFactory;

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'document_type',
        'document_number',
        'first_name',
        'last_name',
        'birth_date',
        'phones',
        'addresses',
        'photo_path',
        'is_active',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'document_type' => DocumentType::class,
            'birth_date' => 'date',
            'phones' => 'array',
            'addresses' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Obtiene el usuario asociado al empleado.
     *
     * @return BelongsTo<User, Employee>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtiene el nombre completo del empleado.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
