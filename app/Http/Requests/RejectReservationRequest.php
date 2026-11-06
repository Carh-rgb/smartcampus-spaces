<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Rechazo de una reserva: el motivo es obligatorio para que el solicitante
 * sepa por qué no se aprobó. Usa un campo propio (rejection_reason) para que
 * su error no se muestre también en el campo de observación de la aprobación.
 */
class RejectReservationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'rejection_reason' => ['required', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'rejection_reason.required' => 'Escribe el motivo del rechazo para que el solicitante sepa por qué no se aprobó.',
        ];
    }
}
