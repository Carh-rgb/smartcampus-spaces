<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Asignacion del responsable de un incidente (debe ser administrador).
 */
class AssignIncidentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'assigned_to' => ['required', Rule::exists('users', 'id')->where('role', 'admin')],
        ];
    }
}
