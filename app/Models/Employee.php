<?php

namespace App\Models;

use App\Enums\DocumentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

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

    /**
     * Relación: Employee pertenece a un User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function getTitleAttribute()
    {
        return $this->full_name;
    }
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name . ' - ' . $this->document_number;
    }
}
