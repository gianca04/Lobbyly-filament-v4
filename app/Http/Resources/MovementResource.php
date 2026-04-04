<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Movement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Recurso API para transformar el modelo Movement en una respuesta JSON.
 *
 * Formatea la salida incluyendo relaciones anidadas y datos
 * calculados como la etiqueta del tipo y la cantidad con signo.
 *
 * @property Movement $resource
 */
class MovementResource extends JsonResource
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
            'item_id' => $this->resource->item_id,
            'location_id' => $this->resource->location_id,
            'user_id' => $this->resource->user_id,
            'type' => $this->resource->type->value,
            'type_label' => $this->resource->type->label(),
            'quantity' => (float) $this->resource->quantity,
            'signed_quantity' => $this->resource->getSignedQuantity(),
            'notes' => $this->resource->notes,
            'created_at' => $this->resource->created_at?->toIso8601String(),
            'updated_at' => $this->resource->updated_at?->toIso8601String(),
            'item' => $this->whenLoaded('item', fn () => [
                'id' => $this->resource->item->id,
                'name' => $this->resource->item->name,
                'sku' => $this->resource->item->sku,
                'current_stock' => (float) $this->resource->item->current_stock,
            ]),
            'location' => $this->whenLoaded('location', fn () => [
                'id' => $this->resource->location->id,
                'name' => $this->resource->location->name,
            ]),
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
