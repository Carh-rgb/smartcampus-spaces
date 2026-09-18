<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Entidad "espacios" de la Actividad 1: aulas, laboratorios, auditorios
     * y salas de reunion disponibles para reserva.
     */
    public function up(): void
    {
        Schema::create('spaces', function (Blueprint $table) {
            $table->id();                                   // id_espacio
            $table->string('name', 100)->unique();          // nombre_espacio
            $table->string('building', 50);                 // ubicacion_bloque
            $table->unsignedSmallInteger('capacity')->default(0);
            $table->enum('type', ['classroom', 'lab', 'auditorium', 'meeting_room'])
                ->default('classroom');
            $table->enum('status', ['available', 'maintenance', 'inactive'])
                ->default('available');                     // estado
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Consulta mas frecuente: espacios disponibles por bloque.
            $table->index(['status', 'building']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spaces');
    }
};
