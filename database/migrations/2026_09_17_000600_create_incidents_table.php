<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Entidad "incidentes" de la Actividad 1: fallas o novedades reportadas
     * sobre un espacio previamente reservado.
     */
    public function up(): void
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();                                   // id_incidente

            $table->foreignId('reservation_id')
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');

            // Usuario que reporta la novedad.
            $table->foreignId('user_id')
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->string('title', 120);
            $table->text('description');                    // descripcion
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->enum('status', ['open', 'in_progress', 'resolved'])->default('open');

            // Administrador o tecnico asignado a la solucion.
            $table->foreignId('assigned_to')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->text('solution')->nullable();
            $table->timestamp('reported_at')->useCurrent(); // fecha_reporte
            $table->timestamp('resolved_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
