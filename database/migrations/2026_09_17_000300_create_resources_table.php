<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Entidad "recursos" de la Actividad 1: catalogo del equipamiento
     * tecnologico del campus (proyectores, tableros, aires, etc.).
     */
    public function up(): void
    {
        Schema::create('resources', function (Blueprint $table) {
            $table->id();                                   // id_recurso
            $table->string('name', 100)->unique();          // nombre_recurso
            $table->string('description')->nullable();
            $table->enum('status', ['operational', 'maintenance', 'out_of_service'])
                ->default('operational');                   // estado_recurso
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};
