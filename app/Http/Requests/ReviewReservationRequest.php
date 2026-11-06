<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Aprobación de una reserva: la observación del administrador es opcional.
 */
class ReviewReservationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'review_notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
