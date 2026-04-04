<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request para validar una transferencia entre ubicaciones.
 *
 * Valida que origen y destino existan, sean diferentes entre sí,
 * y que la cantidad sea positiva.
 */
class StoreTransferMovementRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado a realizar esta petición.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación para la transferencia.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'item_id' => ['required', 'integer', 'exists:items,id'],
            'origin_location_id' => ['required', 'integer', 'exists:locations,id'],
            'destination_location_id' => ['required', 'integer', 'exists:locations,id', 'different:origin_location_id'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Mensajes de error personalizados en español.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'item_id.required' => 'El artículo es obligatorio.',
            'item_id.exists' => 'El artículo seleccionado no existe.',
            'origin_location_id.required' => 'La ubicación de origen es obligatoria.',
            'origin_location_id.exists' => 'La ubicación de origen no existe.',
            'destination_location_id.required' => 'La ubicación de destino es obligatoria.',
            'destination_location_id.exists' => 'La ubicación de destino no existe.',
            'destination_location_id.different' => 'La ubicación de destino debe ser distinta a la de origen.',
            'quantity.required' => 'La cantidad es obligatoria.',
            'quantity.min' => 'La cantidad debe ser mayor a cero.',
            'notes.max' => 'Las observaciones no deben exceder los 1000 caracteres.',
        ];
    }
}
