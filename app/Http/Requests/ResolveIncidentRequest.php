<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Cierre del incidente con la solución aplicada.
 */
class ResolveIncidentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'solution' => ['required', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'solution.required' => 'Describe la solución aplicada para poder cerrar el incidente.',
        ];
    }
}
