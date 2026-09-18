<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seeder principal de SmartCampus Spaces.
 * Se ejecuta con: php artisan migrate:fresh --seed
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,       // usuarios y roles del campus
            SpaceSeeder::class,      // aulas, laboratorios, auditorios
            ResourceSeeder::class,   // recursos + pivote resource_space
            ReservationSeeder::class,// reservas en distintos estados
            IncidentSeeder::class,   // incidentes reportados
        ]);
    }
}
