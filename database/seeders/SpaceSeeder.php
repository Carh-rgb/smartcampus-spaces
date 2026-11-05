<?php

namespace Database\Seeders;

use App\Models\Space;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Espacios fisicos reales del campus disponibles para reserva.
 *
 * Listado fijo y sin relaciones dependientes, por lo que se inserta en
 * bloque con Space::insert() dentro de una transaccion (ver PHPDoc de
 * UserSeeder para la explicacion completa del patron y de por que el
 * truncado se hace antes de abrir la transaccion).
 */
class SpaceSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Space::truncate();

        try {
            DB::beginTransaction();

            $now = now();

            $datos = [
                ['name' => 'Laboratorio de Computo 1', 'building' => 'Bloque A', 'capacity' => 30, 'type' => 'lab', 'status' => 'available', 'description' => null, 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Laboratorio de Computo 2', 'building' => 'Bloque A', 'capacity' => 25, 'type' => 'lab', 'status' => 'available', 'description' => null, 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Aula 201', 'building' => 'Bloque B', 'capacity' => 40, 'type' => 'classroom', 'status' => 'available', 'description' => null, 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Aula 202', 'building' => 'Bloque B', 'capacity' => 40, 'type' => 'classroom', 'status' => 'maintenance', 'description' => null, 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Auditorio Principal', 'building' => 'Bloque C', 'capacity' => 150, 'type' => 'auditorium', 'status' => 'available', 'description' => null, 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Sala de Reuniones Docentes', 'building' => 'Bloque C', 'capacity' => 12, 'type' => 'meeting_room', 'status' => 'available', 'description' => null, 'created_at' => $now, 'updated_at' => $now],
            ];

            // Insertar todos los espacios en una sola consulta SQL.
            Space::insert($datos);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }
}
