<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla pivote que resuelve la relacion N:M entre espacios y recursos
     * (Actividad 1: espacio_recursos). El nombre sigue la convencion:
     * los dos modelos en singular, en orden alfabetico -> resource_space.
     */
    public function up(): void
    {
        Schema::create('resource_space', function (Blueprint $table) {
            $table->id();                                   // id_espacio_recurso

            $table->foreignId('resource_id')
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreignId('space_id')
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->unsignedSmallInteger('quantity')->default(1); // cantidad
            $table->timestamps();

            // Un mismo recurso no puede repetirse dentro del mismo espacio.
            $table->unique(['resource_id', 'space_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resource_space');
    }
};
