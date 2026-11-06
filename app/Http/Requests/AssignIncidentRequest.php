<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Asignación del responsable de un incidente (debe ser administrador).
 */
class AssignIncidentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'assigned_to' => ['required', Rule::exists('users', 'id')->where('role', 'admin')],
        ];
    }

    public function messages(): array
    {
        return [
            'assigned_to.required' => 'Selecciona quién se encargará del incidente.',
            'assigned_to.exists' => 'El responsable debe ser un administrador.',
        ];
    }
}
