<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * Usuarios iniciales del sistema: un administrador del campus y
 * solicitantes de prueba (docentes y estudiantes).
 *
 * Sigue el patron obligatorio de transaccion + manejo de excepciones:
 * los tres usuarios nombrados (admin, docente y estudiante de referencia)
 * son datos fijos conocidos de antemano, por lo que se insertan en una
 * sola consulta SQL con User::insert(). Los usuarios adicionales
 * aleatorios se generan con factories porque su contenido es aleatorio
 * y no tiene sentido "quemarlo" en un arreglo estatico.
 *
 * Nota: el truncado se hace ANTES de abrir la transaccion. En MySQL,
 * TRUNCATE hace un commit implicito y no es transaccional (a diferencia
 * de DELETE); si se ejecuta dentro de un beginTransaction(), MySQL cierra
 * la transaccion por su cuenta y el DB::commit() final falla con
 * "There is no active transaction".
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        User::truncate();

        try {
            DB::beginTransaction();

            $now = now();

            $datos = [
                [
                    'name' => 'Administrador General',
                    'email' => 'admin@example.com',
                    // static::insert() no dispara el cast 'hashed' del modelo
                    // (eso solo ocurre al guardar con Eloquent), por lo que la
                    // contrasena se hashea manualmente antes de insertarla.
                    'password' => Hash::make('password'),
                    'role' => 'admin',
                    'is_active' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'name' => 'Docente de Prueba',
                    'email' => 'docente@example.com',
                    'password' => Hash::make('password'),
                    'role' => 'teacher',
                    'is_active' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'name' => 'Estudiante de Prueba',
                    'email' => 'estudiante@example.com',
                    'password' => Hash::make('password'),
                    'role' => 'student',
                    'is_active' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ];

            // Insertar los datos fijos en una sola consulta SQL.
            User::insert($datos);

            // Usuarios adicionales de prueba generados con factories
            // (contenido aleatorio: no forma parte del insert en bloque).
            User::factory(3)->teacher()->create();
            User::factory(8)->student()->create();

            DB::commit();
        } catch (\Throwable $th) {
            // Revertir todos los cambios de esta transaccion para mantener
            // la base de datos en su estado original antes del seeder.
            DB::rollBack();

            // Relanzar la excepcion para que sea visible en la consola de
            // Artisan en vez de fallar en silencio.
            throw $th;
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }
}
