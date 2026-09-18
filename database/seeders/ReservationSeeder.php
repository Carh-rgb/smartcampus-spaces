<?php

namespace Database\Seeders;

use App\Models\Reservation;
use App\Models\Space;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Reservas de prueba en distintos estados (pendiente, aprobada, rechazada).
 */
class ReservationSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();
        $teacher = User::where('email', 'docente@uajs.edu.co')->firstOrFail();
        $student = User::where('email', 'estudiante@uajs.edu.co')->firstOrFail();

        $lab = Space::where('name', 'Laboratorio de Computo 1')->firstOrFail();
        $auditorium = Space::where('name', 'Auditorio Principal')->firstOrFail();
        $classroom = Space::where('name', 'Aula 201')->firstOrFail();

        Reservation::factory()->approved($admin)->create([
            'user_id' => $teacher->id,
            'space_id' => $lab->id,
            'date' => now()->addDay()->toDateString(),
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'purpose' => 'Clase practica de Bases de Datos II',
        ]);

        Reservation::factory()->create([
            'user_id' => $student->id,
            'space_id' => $classroom->id,
            'date' => now()->addDays(2)->toDateString(),
            'start_time' => '14:00:00',
            'end_time' => '16:00:00',
            'purpose' => 'Reunion del semillero de investigacion',
            'status' => 'pending',
        ]);

        Reservation::factory()->rejected($admin)->create([
            'user_id' => $student->id,
            'space_id' => $auditorium->id,
            'date' => now()->addDays(3)->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
            'purpose' => 'Ensayo de grupo cultural',
        ]);

        // Reservas aleatorias adicionales para pruebas de consulta.
        Reservation::factory(10)
            ->recycle(User::where('role', '!=', 'admin')->get())
            ->recycle(Space::where('status', 'available')->get())
            ->create();
    }
}
