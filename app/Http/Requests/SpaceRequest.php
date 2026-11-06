<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validación del formulario de espacios (store y update).
 * La autorización la resuelve SpacePolicy desde el controlador.
 */
class SpaceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('spaces', 'name')->ignore($this->route('space'))],
            'building' => ['required', 'string', 'max:50'],
            'capacity' => ['required', 'integer', 'min:1', 'max:1000'],
            'type' => ['required', Rule::in(['classroom', 'lab', 'auditorium', 'meeting_room'])],
            'status' => ['required', Rule::in(['available', 'maintenance', 'inactive'])],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Mensajes propios que explican cómo corregir el dato.
     */
    public function messages(): array
    {
        return [
            'name.unique' => 'Ya existe un espacio con ese nombre. Usa un nombre distinto, por ejemplo agregando el número o el bloque.',
            'capacity.integer' => 'La capacidad debe ser un número entero de personas.',
            'capacity.min' => 'La capacidad debe ser de al menos 1 persona.',
            'capacity.max' => 'La capacidad no puede superar las 1000 personas.',
            'type.in' => 'Selecciona un tipo de espacio de la lista.',
            'status.in' => 'Selecciona un estado de la lista.',
        ];
    }
}
