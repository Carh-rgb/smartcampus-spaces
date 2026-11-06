<?php

namespace App\Http\Requests;

use App\Models\Reservation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Validacion de la solicitud de reserva (store y update), incluida la
 * regla de negocio principal: no se permiten cruces de horario.
 */
class ReservationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'space_id' => ['required', 'exists:spaces,id'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'purpose' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * Se ejecuta despues de las reglas basicas. Ignora las reservas
     * rechazadas y canceladas, que si liberan la franja horaria.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $cruce = Reservation::query()->overlapping(
                    (int) $this->input('space_id'),
                    $this->input('date'),
                    $this->input('start_time'),
                    $this->input('end_time'),
                    $this->route('reservation')?->getKey(),
                )->exists();

                if ($cruce) {
                    $validator->errors()->add('start_time', 'El espacio ya tiene una reserva activa que se cruza con ese horario.');
                }
            },
        ];
    }
}
