<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validación del formulario de recursos tecnológicos (store y update).
 */
class ResourceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('resources', 'name')->ignore($this->route('resource'))],
            'description' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['operational', 'maintenance', 'out_of_service'])],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Ese recurso ya está en el catálogo.',
            'status.in' => 'Selecciona un estado de la lista.',
        ];
    }
}
