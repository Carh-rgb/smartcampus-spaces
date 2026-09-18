<?php

namespace Database\Seeders;

use App\Models\Resource;
use App\Models\Space;
use Illuminate\Database\Seeder;

/**
 * Catalogo de recursos tecnologicos y su asignacion a cada espacio
 * (llena tambien la tabla pivote resource_space con su cantidad).
 */
class ResourceSeeder extends Seeder
{
    public function run(): void
    {
        $resources = [
            ['name' => 'Video Beam',                'description' => 'Proyector multimedia HDMI',        'status' => 'operational'],
            ['name' => 'Tablero Inteligente',       'description' => 'Tablero digital interactivo',      'status' => 'operational'],
            ['name' => 'Aire Acondicionado',        'description' => 'Unidad de 24000 BTU',              'status' => 'operational'],
            ['name' => 'Computador de Escritorio',  'description' => 'Equipo de laboratorio i5/8GB',     'status' => 'operational'],
            ['name' => 'Sistema de Sonido',         'description' => 'Amplificador y parlantes',         'status' => 'maintenance'],
            ['name' => 'Microfono Inalambrico',     'description' => 'Microfono de solapa recargable',   'status' => 'operational'],
        ];

        foreach ($resources as $resource) {
            Resource::create($resource);
        }

        // Asignacion de recursos a cada espacio (relacion N:M con cantidad).
        $assignments = [
            'Laboratorio de Computo 1' => ['Computador de Escritorio' => 30, 'Video Beam' => 1, 'Aire Acondicionado' => 2],
            'Laboratorio de Computo 2' => ['Computador de Escritorio' => 25, 'Video Beam' => 1, 'Aire Acondicionado' => 2],
            'Aula 201' => ['Video Beam' => 1, 'Tablero Inteligente' => 1, 'Aire Acondicionado' => 1],
            'Aula 202' => ['Video Beam' => 1, 'Aire Acondicionado' => 1],
            'Auditorio Principal' => ['Video Beam' => 2, 'Sistema de Sonido' => 1, 'Microfono Inalambrico' => 4, 'Aire Acondicionado' => 4],
            'Sala de Reuniones Docentes' => ['Tablero Inteligente' => 1, 'Aire Acondicionado' => 1],
        ];

        foreach ($assignments as $spaceName => $items) {
            $space = Space::where('name', $spaceName)->first();

            if (! $space) {
                continue;
            }

            foreach ($items as $resourceName => $quantity) {
                $resource = Resource::where('name', $resourceName)->first();

                if ($resource) {
                    $space->resources()->attach($resource->id, ['quantity' => $quantity]);
                }
            }
        }
    }
}
