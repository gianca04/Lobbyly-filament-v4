<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Enums\DocumentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Employee extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'tipo_documento',
        'numero_documento',
        'nombres',
        'apellidos',
        'fecha_nacimiento',
        'telefono',
        'direccion',
        'es_activo',
        'photo_path',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tipo_documento' => DocumentType::class,
            'fecha_nacimiento' => 'date',
            'telefono' => 'array',
            'direccion' => 'array',
            'es_activo' => 'boolean',
        ];
    }
}
