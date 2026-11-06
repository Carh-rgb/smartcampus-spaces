<?php

namespace Database\Seeders;

use App\Models\Resource;
use App\Models\Space;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Catalogo de recursos tecnologicos y su asignacion a cada espacio
 * (llena tambien la tabla pivote resource_space con su cantidad).
 *
 * Excepcion documentada: el catalogo de recursos es un listado fijo y se
 * inserta en bloque con Resource::insert(). La asignacion a espacios, en
 * cambio, necesita consultar los ids ya generados de 'spaces' (creados
 * por SpaceSeeder) y de 'resources' (creados aqui mismo unas lineas
 * antes), por lo que no puede resolverse con una insercion masiva y se
 * mantiene con consultas Eloquent normales dentro de la misma transaccion.
 */
class ResourceSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Resource::truncate();
        DB::table('resource_space')->truncate();

        try {
            DB::beginTransaction();

            $now = now();

            $datos = [
                ['name' => 'Video Beam', 'description' => 'Proyector multimedia HDMI', 'status' => 'operational', 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Tablero Inteligente', 'description' => 'Tablero digital interactivo', 'status' => 'operational', 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Aire Acondicionado', 'description' => 'Unidad de 24000 BTU', 'status' => 'operational', 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Computador de Escritorio', 'description' => 'Equipo de laboratorio i5/8GB', 'status' => 'operational', 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Sistema de Sonido', 'description' => 'Amplificador y parlantes', 'status' => 'maintenance', 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'Microfono Inalambrico', 'description' => 'Microfono de solapa recargable', 'status' => 'operational', 'created_at' => $now, 'updated_at' => $now],
            ];

            // Insertar el catalogo de recursos en una sola consulta SQL.
            Resource::insert($datos);

            // Asignacion de recursos a cada espacio (relacion N:M con cantidad).
            $assignments = [
                'Laboratorio de Cómputo 1' => ['Computador de Escritorio' => 30, 'Video Beam' => 1, 'Aire Acondicionado' => 2],
                'Laboratorio de Cómputo 2' => ['Computador de Escritorio' => 25, 'Video Beam' => 1, 'Aire Acondicionado' => 2],
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

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }
}
