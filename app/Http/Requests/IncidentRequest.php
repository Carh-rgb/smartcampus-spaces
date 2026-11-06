<?php

namespace App\Http\Requests;

use App\Models\Reservation;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validación del reporte de incidentes (store y update).
 */
class IncidentRequest extends FormRequest
{
    /**
     * Nadie reporta sobre una reserva ajena, salvo el administrador.
     */
    public function authorize(): bool
    {
        $reservationId = $this->input('reservation_id');

        if (! $reservationId || $this->user()->isAdmin()) {
            return true;
        }

        return Reservation::whereKey($reservationId)->where('user_id', $this->user()->id)->exists();
    }

    public function rules(): array
    {
        return [
            // Solo reservas que ya ocurrieron o se liberaron (aprobadas o canceladas).
            'reservation_id' => ['required', Rule::exists('reservations', 'id')->whereIn('status', ['approved', 'cancelled'])->whereNull('deleted_at')],
            'title' => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'max:1000'],
            'priority' => ['required', Rule::in(['low', 'medium', 'high'])],
        ];
    }

    public function messages(): array
    {
        return [
            'reservation_id.required' => 'Selecciona la reserva en la que ocurrió la novedad.',
            'reservation_id.exists' => 'Solo se pueden reportar novedades sobre reservas aprobadas o canceladas.',
            'title.required' => 'Escribe un título corto, por ejemplo "Video beam no enciende".',
            'description.required' => 'Describe qué pasó para que el administrador pueda solucionarlo.',
            'priority.in' => 'Selecciona una prioridad de la lista.',
        ];
    }

    protected function failedAuthorization(): void
    {
        throw new AuthorizationException('Solo se puede reportar sobre una reserva propia.');
    }
}
