<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Cierre del incidente con la solucion aplicada.
 */
class ResolveIncidentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'solution' => ['required', 'string', 'max:500'],
        ];
    }
}
