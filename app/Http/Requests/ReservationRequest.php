<?php

namespace App\Http\Requests;

use App\Models\Reservation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Validación de la solicitud de reserva (store y update), incluida la
 * regla de negocio principal: no se permiten cruces de horario.
 */
class ReservationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // Solo espacios disponibles: uno en mantenimiento o eliminado no se reserva,
            // aunque alguien envíe el formulario a mano.
            'space_id' => ['required', Rule::exists('spaces', 'id')->where('status', 'available')->whereNull('deleted_at')],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'purpose' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'space_id.required' => 'Selecciona el espacio que quieres reservar.',
            'space_id.exists' => 'El espacio seleccionado no está disponible para reservas.',
            'date.after_or_equal' => 'La fecha no puede ser anterior a hoy.',
            'start_time.date_format' => 'Escribe la hora de inicio en formato HH:MM, por ejemplo 08:00.',
            'end_time.date_format' => 'Escribe la hora de fin en formato HH:MM, por ejemplo 10:00.',
            'end_time.after' => 'La hora de fin debe ser posterior a la hora de inicio.',
            'purpose.required' => 'Cuéntanos para qué vas a usar el espacio.',
        ];
    }

    /**
     * Se ejecuta después de las reglas básicas. Ignora las reservas
     * rechazadas y canceladas, que sí liberan la franja horaria.
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
                )->first();

                if ($cruce) {
                    $validator->errors()->add('start_time', sprintf(
                        'El espacio ya tiene una reserva activa de %s a %s que se cruza con ese horario.',
                        substr($cruce->start_time, 0, 5),
                        substr($cruce->end_time, 0, 5),
                    ));
                }
            },
        ];
    }
}
