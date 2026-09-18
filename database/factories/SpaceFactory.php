<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Space>
 */
class SpaceFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement(['classroom', 'lab', 'auditorium', 'meeting_room']);

        $prefix = match ($type) {
            'lab' => 'Laboratorio',
            'auditorium' => 'Auditorio',
            'meeting_room' => 'Sala de Reuniones',
            default => 'Aula',
        };

        return [
            'name' => $prefix.' '.fake()->unique()->numberBetween(100, 999),
            'building' => 'Bloque '.fake()->randomElement(['A', 'B', 'C', 'D']),
            'capacity' => fake()->numberBetween(10, 120),
            'type' => $type,
            'status' => 'available',
            'description' => fake()->optional()->sentence(8),
        ];
    }

    /**
     * Espacio fuera de servicio por mantenimiento.
     */
    public function maintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'maintenance',
        ]);
    }
}
