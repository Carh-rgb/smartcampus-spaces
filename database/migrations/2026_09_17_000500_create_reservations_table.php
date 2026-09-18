<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Entidad "reservas" de la Actividad 1: solicitudes de uso de un espacio
     * en una fecha y franja horaria, con su estado de aprobacion.
     */
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();                                   // id_reserva

            // Solicitante: al eliminar el usuario se eliminan sus reservas.
            $table->foreignId('user_id')
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreignId('space_id')
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->date('date');                           // fecha
            $table->time('start_time');                     // hora_inicio
            $table->time('end_time');                       // hora_fin
            $table->string('purpose', 255);                 // razon de uso
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])
                ->default('pending');                       // estado_reserva

            // Administrador que aprueba o rechaza: la reserva sobrevive si se borra.
            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indices para validar disponibilidad y solapamiento de horarios.
            $table->index(['space_id', 'date', 'start_time']);
            $table->index(['status', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
