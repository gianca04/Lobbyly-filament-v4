<?php

namespace App\Http\Resources;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Recurso para transformar el modelo Employee en una respuesta JSON.
 *
 * @property Employee $resource
 */
class EmployeeResource extends JsonResource
{
    /**
     * Transforma el recurso en un array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'user_id' => $this->resource->user_id,
            'document_type' => $this->resource->document_type->value,
            'document_type_label' => $this->resource->document_type->label(),
            'document_number' => $this->resource->document_number,
            'first_name' => $this->resource->first_name,
            'last_name' => $this->resource->last_name,
            'full_name' => $this->resource->full_name,
            'birth_date' => $this->resource->birth_date->toDateString(),
            'phones' => $this->resource->phones,
            'addresses' => $this->resource->addresses,
            'photo_url' => $this->resource->photo_path ? asset('storage/'.$this->resource->photo_path) : null,
            'is_active' => $this->resource->is_active,
            'created_at' => $this->resource->created_at?->toIso8601String(),
            'updated_at' => $this->resource->updated_at?->toIso8601String(),
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
