<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validacion del formulario de espacios (store y update).
 * La autorizacion la resuelve SpacePolicy desde el controlador.
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
}
