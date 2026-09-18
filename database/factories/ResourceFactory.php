<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Resource>
 */
class ResourceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'Video Beam', 'Tablero Inteligente', 'Aire Acondicionado',
                'Computador de Escritorio', 'Sistema de Sonido', 'Televisor 55"',
                'Microfono Inalambrico', 'Router WiFi',
            ]),
            'description' => fake()->optional()->sentence(6),
            'status' => 'operational',
        ];
    }

    /**
     * Recurso reportado fuera de servicio.
     */
    public function outOfService(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'out_of_service',
        ]);
    }
}
