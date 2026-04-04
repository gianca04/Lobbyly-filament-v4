<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request para validar un ajuste de inventario.
 *
 * Valida que el artículo y ubicación existan, y que la nueva
 * cantidad (conteo real) sea un número no negativo.
 */
class StoreAdjustmentMovementRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado a realizar esta petición.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación para el ajuste de inventario.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'item_id' => ['required', 'integer', 'exists:items,id'],
            'location_id' => ['required', 'integer', 'exists:locations,id'],
            'new_quantity' => ['required', 'numeric', 'min:0'],
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
            'location_id.required' => 'La ubicación es obligatoria.',
            'location_id.exists' => 'La ubicación seleccionada no existe.',
            'new_quantity.required' => 'La cantidad contabilizada es obligatoria.',
            'new_quantity.min' => 'La cantidad contabilizada no puede ser negativa.',
            'notes.max' => 'Las observaciones no deben exceder los 1000 caracteres.',
        ];
    }
}
