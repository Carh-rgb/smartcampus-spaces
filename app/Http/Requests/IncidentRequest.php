<?php

namespace App\Http\Requests;

use App\Models\Reservation;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validacion del reporte de incidentes (store y update).
 */
class IncidentRequest extends FormRequest
{
    /**
     * Nadie reporta sobre una reserva ajena, salvo el administrador.
     */
    public function authorize(): bool
    {
        // Si falla, failedAuthorization() responde 403 con el mensaje de abajo.
        $reservationId = $this->input('reservation_id');

        if (! $reservationId || $this->user()->isAdmin()) {
            return true;
        }

        return Reservation::whereKey($reservationId)->where('user_id', $this->user()->id)->exists();
    }

    public function rules(): array
    {
        return [
            'reservation_id' => ['required', 'exists:reservations,id'],
            'title' => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'max:1000'],
            'priority' => ['required', Rule::in(['low', 'medium', 'high'])],
        ];
    }

    protected function failedAuthorization(): void
    {
        throw new AuthorizationException('Solo se puede reportar sobre una reserva propia.');
    }
}
