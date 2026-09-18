<?php

namespace Database\Factories;

use App\Models\Space;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reservation>
 */
class ReservationFactory extends Factory
{
    public function definition(): array
    {
        $start = fake()->numberBetween(6, 18);

        return [
            'user_id' => User::factory(),
            'space_id' => Space::factory(),
            'date' => fake()->dateTimeBetween('-10 days', '+20 days')->format('Y-m-d'),
            'start_time' => sprintf('%02d:00:00', $start),
            'end_time' => sprintf('%02d:00:00', $start + 2),
            'purpose' => fake()->randomElement([
                'Clase de Bases de Datos II',
                'Sustentacion de proyecto de grado',
                'Reunion de semillero de investigacion',
                'Taller practico de programacion',
                'Capacitacion docente',
            ]),
            'status' => 'pending',
        ];
    }

    /**
     * Reserva ya aprobada por un administrador.
     */
    public function approved(?User $approver = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'approved_by' => $approver?->id ?? User::factory()->admin(),
            'reviewed_at' => now(),
        ]);
    }

    /**
     * Reserva rechazada por cruce de horario u otra razon.
     */
    public function rejected(?User $approver = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'approved_by' => $approver?->id ?? User::factory()->admin(),
            'reviewed_at' => now(),
            'review_notes' => 'El espacio ya se encuentra asignado en esa franja horaria.',
        ]);
    }
}
