<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request para validar un ingreso masivo de inventario.
 *
 * Valida que el artículo exista, que se proporcione al menos una
 * distribución con ubicación válida y cantidad positiva.
 */
class StoreInputMovementRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado a realizar esta petición.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación para el ingreso masivo.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'item_id' => ['required', 'integer', 'exists:items,id'],
            'distributions' => ['required', 'array', 'min:1'],
            'distributions.*.location_id' => ['required', 'integer', 'exists:locations,id'],
            'distributions.*.quantity' => ['required', 'numeric', 'min:0.01'],
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
            'distributions.required' => 'Debe indicar al menos una distribución.',
            'distributions.min' => 'Debe haber al menos una distribución.',
            'distributions.*.location_id.required' => 'La ubicación es obligatoria para cada distribución.',
            'distributions.*.location_id.exists' => 'La ubicación seleccionada no existe.',
            'distributions.*.quantity.required' => 'La cantidad es obligatoria para cada distribución.',
            'distributions.*.quantity.min' => 'La cantidad debe ser mayor a cero.',
            'notes.max' => 'Las observaciones no deben exceder los 1000 caracteres.',
        ];
    }
}
