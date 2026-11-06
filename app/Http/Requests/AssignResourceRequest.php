<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Asignacion de un recurso a un espacio con su cantidad (tabla pivote).
 */
class AssignResourceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'resource_id' => ['required', 'exists:resources,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:500'],
        ];
    }
}
