<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Asignación de un recurso a un espacio con su cantidad (tabla pivote).
 */
class AssignResourceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'resource_id' => ['required', Rule::exists('resources', 'id')->whereNull('deleted_at')],
            'quantity' => ['required', 'integer', 'min:1', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'resource_id.required' => 'Selecciona el recurso que quieres asignar.',
            'resource_id.exists' => 'El recurso seleccionado ya no está en el catálogo.',
            'quantity.min' => 'La cantidad debe ser de al menos 1 unidad.',
            'quantity.max' => 'La cantidad no puede superar las 500 unidades.',
        ];
    }
}
