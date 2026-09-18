<?php

namespace Database\Factories;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Incident>
 */
class IncidentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'reservation_id' => Reservation::factory(),
            'user_id' => User::factory(),
            'title' => fake()->randomElement([
                'Video beam no enciende',
                'Aire acondicionado con fuga',
                'Tablero inteligente sin calibrar',
                'Sin conexion a la red WiFi',
                'Sillas en mal estado',
            ]),
            'description' => fake()->sentence(12),
            'priority' => fake()->randomElement(['low', 'medium', 'high']),
            'status' => 'open',
            'reported_at' => now(),
        ];
    }

    /**
     * Incidente ya solucionado por el area de mantenimiento.
     */
    public function resolved(?User $assignee = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'resolved',
            'assigned_to' => $assignee?->id ?? User::factory()->admin(),
            'solution' => 'Equipo revisado y puesto nuevamente en funcionamiento.',
            'resolved_at' => now(),
        ]);
    }
}
