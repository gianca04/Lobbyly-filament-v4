<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request para validar un ajuste masivo de inventario.
 *
 * Valida un array de ajustes donde cada fila puede ser
 * un artículo diferente en una ubicación diferente,
 * con la cantidad real contabilizada.
 */
class StoreBatchAdjustmentRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado a realizar esta petición.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación para el ajuste masivo.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'adjustments' => ['required', 'array', 'min:1'],
            'adjustments.*.item_id' => ['required', 'integer', 'exists:items,id'],
            'adjustments.*.location_id' => ['required', 'integer', 'exists:locations,id'],
            'adjustments.*.new_quantity' => ['required', 'numeric', 'min:0'],
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
            'adjustments.required' => 'Debe indicar al menos un ajuste.',
            'adjustments.min' => 'Debe haber al menos un ajuste.',
            'adjustments.*.item_id.required' => 'El artículo es obligatorio en cada ajuste.',
            'adjustments.*.item_id.exists' => 'El artículo seleccionado no existe.',
            'adjustments.*.location_id.required' => 'La ubicación es obligatoria en cada ajuste.',
            'adjustments.*.location_id.exists' => 'La ubicación seleccionada no existe.',
            'adjustments.*.new_quantity.required' => 'La cantidad contabilizada es obligatoria.',
            'adjustments.*.new_quantity.min' => 'La cantidad contabilizada no puede ser negativa.',
            'notes.max' => 'Las observaciones no deben exceder los 1000 caracteres.',
        ];
    }
}
