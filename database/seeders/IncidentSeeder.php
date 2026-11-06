<?php

namespace Database\Seeders;

use App\Models\Incident;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Incidentes reportados sobre reservas ya realizadas.
 *
 * Excepcion documentada: depende de reservas y usuarios ya existentes
 * (creados por ReservationSeeder y UserSeeder), por lo que se mantiene
 * con Eloquent en vez de insercion masiva (ver PHPDoc de ReservationSeeder).
 */
class IncidentSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Incident::truncate();

        try {
            DB::beginTransaction();

            $admin = User::where('role', 'admin')->firstOrFail();
            $reservation = Reservation::where('status', 'approved')->firstOrFail();

            Incident::factory()->create([
                'reservation_id' => $reservation->id,
                'user_id' => $reservation->user_id,
                'title' => 'Video beam no enciende',
                'description' => 'El proyector del laboratorio no muestra imagen al conectar el portatil.',
                'priority' => 'high',
                'status' => 'in_progress',
                'assigned_to' => $admin->id,
            ]);

            Incident::factory()->resolved($admin)->create([
                'reservation_id' => $reservation->id,
                'user_id' => $reservation->user_id,
                'title' => 'Aire acondicionado sin funcionar',
                'description' => 'La unidad del bloque A no enfria durante la jornada de la manana.',
                'priority' => 'medium',
            ]);

            // Incidentes aleatorios sobre las demas reservas existentes.
            // La factory toma como reportante al solicitante de la reserva.
            Incident::factory(5)
                ->recycle(Reservation::all())
                ->create();

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }
}
