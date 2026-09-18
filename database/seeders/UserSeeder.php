<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Usuarios iniciales del sistema: un administrador del campus y
 * solicitantes de prueba (docentes y estudiantes).
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Administrador del Campus',
            'email' => 'admin@uajs.edu.co',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'Guillermo Antonio Gonzalez Marquez',
            'email' => 'docente@uajs.edu.co',
            'password' => Hash::make('password'),
            'role' => 'teacher',
        ]);

        User::factory()->create([
            'name' => 'Camilo Andres Ricardo Hoyos',
            'email' => 'estudiante@uajs.edu.co',
            'password' => Hash::make('password'),
            'role' => 'student',
        ]);

        // Usuarios adicionales de prueba generados con factories.
        User::factory(3)->teacher()->create();
        User::factory(8)->student()->create();
    }
}
