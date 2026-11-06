<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Decision del administrador: la observacion es opcional al aprobar
 * y obligatoria al rechazar.
 */
class ReviewReservationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'review_notes' => [$this->routeIs('reservations.reject') ? 'required' : 'nullable', 'string', 'max:500'],
        ];
    }
}
