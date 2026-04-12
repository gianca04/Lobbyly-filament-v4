<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request para validar una transferencia masiva entre ubicaciones.
 *
 * Valida un array de transferencias donde cada fila puede ser
 * un artículo diferente moviéndose entre ubicaciones distintas.
 */
class StoreBatchTransferRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado a realizar esta petición.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación para la transferencia masiva.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'transfers' => ['required', 'array', 'min:1'],
            'transfers.*.item_id' => ['required', 'integer', 'exists:items,id'],
            'transfers.*.origin_location_id' => ['required', 'integer', 'exists:locations,id'],
            'transfers.*.destination_location_id' => ['required', 'integer', 'exists:locations,id', 'different:transfers.*.origin_location_id'],
            'transfers.*.quantity' => ['required', 'numeric', 'min:0.01'],
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
            'transfers.required' => 'Debe indicar al menos una transferencia.',
            'transfers.min' => 'Debe haber al menos una transferencia.',
            'transfers.*.item_id.required' => 'El artículo es obligatorio en cada transferencia.',
            'transfers.*.item_id.exists' => 'El artículo seleccionado no existe.',
            'transfers.*.origin_location_id.required' => 'La ubicación de origen es obligatoria.',
            'transfers.*.origin_location_id.exists' => 'La ubicación de origen no existe.',
            'transfers.*.destination_location_id.required' => 'La ubicación de destino es obligatoria.',
            'transfers.*.destination_location_id.exists' => 'La ubicación de destino no existe.',
            'transfers.*.destination_location_id.different' => 'La ubicación de destino debe ser distinta a la de origen.',
            'transfers.*.quantity.required' => 'La cantidad es obligatoria en cada transferencia.',
            'transfers.*.quantity.min' => 'La cantidad debe ser mayor a cero.',
            'notes.max' => 'Las observaciones no deben exceder los 1000 caracteres.',
        ];
    }
}
