<?php

namespace Database\Seeders;

use App\Models\Space;
use Illuminate\Database\Seeder;

/**
 * Espacios fisicos reales del campus disponibles para reserva.
 */
class SpaceSeeder extends Seeder
{
    public function run(): void
    {
        $spaces = [
            ['name' => 'Laboratorio de Computo 1', 'building' => 'Bloque A', 'capacity' => 30, 'type' => 'lab',          'status' => 'available'],
            ['name' => 'Laboratorio de Computo 2', 'building' => 'Bloque A', 'capacity' => 25, 'type' => 'lab',          'status' => 'available'],
            ['name' => 'Aula 201',                 'building' => 'Bloque B', 'capacity' => 40, 'type' => 'classroom',    'status' => 'available'],
            ['name' => 'Aula 202',                 'building' => 'Bloque B', 'capacity' => 40, 'type' => 'classroom',    'status' => 'maintenance'],
            ['name' => 'Auditorio Principal',      'building' => 'Bloque C', 'capacity' => 150, 'type' => 'auditorium',  'status' => 'available'],
            ['name' => 'Sala de Reuniones Docentes', 'building' => 'Bloque C', 'capacity' => 12, 'type' => 'meeting_room', 'status' => 'available'],
        ];

        foreach ($spaces as $space) {
            Space::create($space);
        }
    }
}
